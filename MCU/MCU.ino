#include <Scheduler.h>

#define toggleLed() digitalWrite(LED_BUILTIN, !digitalRead(LED_BUILTIN))

//#define VERBOSE 1 

//----------  TEMPERATURE SENSOR ---------------------

#include <Wire.h>
#include <SPI.h>
#include <Adafruit_BME280.h>

Adafruit_BME280 bme; // use I2C interface
Adafruit_Sensor *bme_temp = bme.getTemperatureSensor();
Adafruit_Sensor *bme_pressure = bme.getPressureSensor();
Adafruit_Sensor *bme_humidity = bme.getHumiditySensor();


float temp, humi, pres;


//---------- GPS RECEIVER -------------------------------
#include <SoftwareSerial.h>
#include <TinyGPS++.h>

TinyGPSPlus gps;
SoftwareSerial GPSerial(14,12);
float Latitude , Longitude, oldLatitude, oldLongitude;
int year;
uint8_t month , day, hour , minutes , seconds , oldMinutes, oldSeconds;
float lastKnownSpeed;


//----------- WI-FI SECTION ---------------------------

#include <ESP8266WiFi.h>


char ssid[] = "Gruppo14";
char pass[] = "12345678";


//------------ HTTP SECTION ----------------------------
#include <ESP8266HTTPClient.h>
WiFiClient client;
char serverURL[] = "http://192.168.1.139:80/getSensorValue.php";


//------------ FILE STORAGE SECTION ---------------------

#include "LittleFS.h"

int tuplesStored, tupleSize;

//--------------------------------------------  FUNCTIONS


/*
 * --------------- GET SENSOR READING
 * Read values from sensors and update global variables
 */
void SensorRead() {

  // CLIMATE
  
  temp=bme.readTemperature();
  humi=bme.readHumidity();
  pres=bme.readPressure()/100.0F;



  // COORDINATES
  
  /* 
   * flush data from serial, because while main was waiting,
   * new (outdated) data must have arrived in serial buffer
   * GPSerial
   * 
   */
  
  #ifdef VERBOSE
  unsigned long t1;
  t1=micros(); //debug var
  #endif

  while (GPSerial.available() > 0) GPSerial.read();
  
  #ifdef VERBOSE
  unsigned long t2;
  t2=micros(); //debug var
  Serial.println("Flushing time: "+String((t2-t1)/1000)+"ms");
  #endif

  /* gps.encode gets one char at a time and 
   *  returns false until the last char has 
   *  completed the "whole picture" of NMEA strings
   *  
   *  
   *  So we have to:
   *  1) assure that there are new chars in buffer
   *      for tinygps to read, unless we like exceptions
   *      
   *      usual inner delay (see code below) 
   *      iterations: 3 or 4 times
   *      
   *  2) repeat until tinygps is satiated
   *      usual gps.encode iterations: 70±40 times
   *  
   *  
   *  Usual time needed: 600ms±400ms
   */
  
  #ifdef VERBOSE
  t1=micros();
  int i=0, j=0; //debug vars
  #endif
  
  toggleLed();
  do{  //wait for chars to arrive
    #ifdef VERBOSE
    j++;
    #endif

    while (GPSerial.available()<=0) { 
      delay(200);
      
      #ifdef VERBOSE
        i++;
      #endif
    }

  //until tinygps is satiated  
  }while (!gps.encode(GPSerial.read()));
  toggleLed();

  
  #ifdef VERBOSE
  t2=micros();
  
  Serial.print("Got out of nested whiles in "+String((t2-t1)/1000)+"ms: ");
  Serial.println("gps.enconde was false "+String(j-1)+" times, and waited for characters "+String(i)+" times"); 
  #endif

  /* 
   *  now gps data is up to date!
   */

  
  Latitude=gps.location.lat();
  Longitude=gps.location.lng();
  
  year=gps.date.year();
  month=gps.date.month();
  day=gps.date.day();
  
  hour=gps.time.hour();
  minutes=gps.time.minute();
  seconds=gps.time.second();
    
}

/*
 * --------------- SEND RESULTS TO SERVER
 * argument: http payload
 * returns http response
 */
int sendToServer(String payload){
  HTTPClient http; //un oggetto http
  
  http.begin(client, "http://"+WiFi.gatewayIP().toString()+":80/getSensorValueM.php");   // TODO http.begin(client, serverURL); for debug purpose, however, it will connect to phone hotspot and forward http requesto to chroot
  
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  int httpCode = http.POST(payload);
  Serial.print("Server response was ");
  Serial.println(httpCode);
  http.end();
  return httpCode;
}

/*
 * ---------------- CALCULATE SPEED AND COMPARE space/time DISTANCE TO 1
 * Calculate if sqrt(DISTANCE/100 + ELAPSEDSECONDS/300)>=1
 * that is to say, either the device has travelled 100 meters, or 5 minutes have elapsed,
 * or else check a quadratic combination of those two conditions
 * also, set lastKnownSpeed global variable
 * 
 * @returns true if condition is verified
 */
 bool calculateSTDistance(){
    double distance;
    
    if(Longitude==0.00 && Latitude==0.00){ //exit because most likely gps has not fix
      return false;
      
    } else distance=gps.distanceBetween(Latitude, Longitude, oldLatitude, oldLongitude);
    
    
    int elapsedTime=(minutes-oldMinutes)*60+seconds-oldSeconds;
    if (elapsedTime<0) elapsedTime+=3600; //for example 18:00:10 - 17:59:59 is -59*60+10-59=-3589 which is only valid if we sum the elapsed hour

    if(elapsedTime==0){  //avoid division by zero
      
      return false;
      
    } else lastKnownSpeed = distance/((double)elapsedTime); //might come in handy when on high speed vehicles there's need for shorter delay
    

    double result=sqrt(distance/100.00+( ((double)elapsedTime))/300.00 ); //remember casts!

    String formulaDebug="sqrt("+String(distance)+"/100+"+String(elapsedTime)+"/300) equals "+String(result);
    
    Serial.println(formulaDebug);
    
    #ifdef VERBOSE
    sendToServer("distanceFormula="+formulaDebug);             // DEBUG
    #endif
   
    if (result>1.00){
      //reset distance and elapsed time
      oldMinutes=minutes;
      oldSeconds=seconds;
      oldLatitude=Latitude; 
      oldLongitude=Longitude;
      return true;
    }
    else return false;
}

/* pop a tuple from memory LIFO
  @returns an http payload ready to
  be uploaded
*/
String pop(){

  if(tuplesStored==0){
    Serial.println("No tuples to pop");
    return "";
  }

  
  uint8_t buffer[tupleSize];
  byte offset=0;
  float* asFloat;
  int* asInt;
  uint8_t* asByte;

  /*
      tuplesSize is 5*sizeof(float)+6*sizeof(int)
      every tuple in the binary file take exactly this space
      to read last tuple we multiply size of N-1
  */
  toggleLed(); //---------------------MEMORY OP
  File file = LittleFS.open("/tuples", "r");

  file.seek(tupleSize* (tuplesStored - 1), SeekSet);

  file.read(buffer, tupleSize);

  file.close();
  toggleLed(); //---------------------/MEMORY OP

  #ifdef VERBOSE
  for (int i=0;i<tupleSize; i++){
    Serial.print(buffer[i]);
    Serial.print(" ");
  }
  Serial.println();
  #endif

  /* for each variable:
   *
   * 1) take the address of the variable
   * 2) cast it to float pointer. Through this pointer, everyone of the
   *    sizeof(float) bytes are put together. We don't even care about endianness
   * 3) increase offset to take into account that sizeof(float) bytes are already read
   * 
   */
  asFloat=(float*)&(buffer[offset]);
  String payload= "temperature="+ String(*asFloat);
  offset+=sizeof(temp);

  asFloat=(float*)&(buffer[offset]);
  payload+="&humidity="+String(*asFloat);
  offset+=sizeof(humi);
  
  asFloat=(float*)&(buffer[offset]);
  payload+="&pressure="+String(*asFloat);
  offset+=sizeof(pres);
  
  asFloat=(float*)&(buffer[offset]);
  payload+="&Longitude="+String(*asFloat);
  offset+=sizeof(Longitude);
  
  asFloat=(float*)&(buffer[offset]);
  payload+="&Latitude="+String(*asFloat);
  offset+=sizeof(Latitude);
  
  asInt=(int*)&(buffer[offset]);
  payload+="&year="+String(*asInt);
  offset+=sizeof(year);

  asByte=(uint8_t*)&(buffer[offset]);
  payload+="&month="+String(*asByte);
  offset+=sizeof(month);

  asByte=(uint8_t*)&(buffer[offset]);
  payload+="&day="+String(*asByte);
  offset+=sizeof(day);

  asByte=(uint8_t*)&(buffer[offset]);
  payload+="&hour="+String(*asByte);
  offset+=sizeof(hour);

  asByte=(uint8_t*)&(buffer[offset]);
  payload+="&minute="+String(*asByte);
  offset+=sizeof(minutes);

  asByte=(uint8_t*)&(buffer[offset]);
  payload+="&second="+String(*asByte);

  return payload;

}


/*
 * push global variables to memory in binary format
 */
void push(){

  uint8_t *toWrite;
  uint8_t buffer[tupleSize];
  byte i, offset=0;


  /*
   * for each variable:
   * 
   * 1) Take its address
   * 2) cast it to a pointer to byte
   * 3) use the pointer to iterate over every byte from 0 to sizeof(var)-1
   * 4) each of those bytes go into a buffer that will be written as char array
   */
  toWrite=(uint8_t*)&temp;
  for(i=0; i<sizeof(temp); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(temp);

  toWrite=(uint8_t*)&humi;
  for(i=0; i<sizeof(humi); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(humi);

  toWrite=(uint8_t*)&pres;
  for(i=0; i<sizeof(pres); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(pres);

  toWrite=(uint8_t*)&Latitude;
  for(i=0; i<sizeof(Latitude); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(Latitude);

  toWrite=(uint8_t*)&Longitude;
  for(i=0; i<sizeof(Longitude); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(Longitude);
  
  toWrite=(uint8_t*)&year;
  for(i=0; i<sizeof(year); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(year);

  toWrite=(uint8_t*)&month;  // to uint8_t
  for(i=0; i<sizeof(month); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(month);

  toWrite=(uint8_t*)&day;    // to uint8_t
  for(i=0; i<sizeof(day); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(day);
  
  toWrite=(uint8_t*)&hour;    // to uint8_t
  for(i=0; i<sizeof(day); i++){
    buffer[offset+i]=toWrite[i];
   }
  offset+=sizeof(day);
  
  toWrite=(uint8_t*)&minutes;    // to uint8_t
  for(i=0; i<sizeof(minutes); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(minutes);

  toWrite=(uint8_t*)&seconds;    // to uint8_t
  for(i=0; i<sizeof(seconds); i++){
    buffer[offset+i]=toWrite[i];
  }
  
  #ifdef VERBOSE
  for (i=0;i<tupleSize; i++){
    Serial.print(buffer[i]);
    Serial.print(" ");
  }
  #endif

  toggleLed(); //------------------------ MEMORY OP
  File file = LittleFS.open("/tuples", "r+");
  file.seek(tupleSize*tuplesStored, SeekSet);

  file.write(buffer, tupleSize);

  file.close();
  toggleLed(); //------------------------/MEMORY OP

  tuplesStored++;
}

/* loop 1:
 * make measurements
 */
class MeasureTask : public Task
{
protected:
    /*void setup()
    {
     
    }*/

    void loop()
    {
        SensorRead();

        /* Calculate if sqrt(DISTANCE/100 + ELAPSEDSECONDS/300)>=1
         *  that is to say, either the device has travelled 100 meters, or 5 minutes have elapsed, or else check a quadratic combination of those two conditions
         */
        enoughSTDistance=calculateSTDistance();

        /*
         *  If connected, upload data
         *  else save data for later
         */
        if (enoughSTDistance)
        {

            if (WiFi.status() == WL_CONNECTED){
              String httpPayload = "humidity=" + String(humi) +
                                   "&temperature=" + String(temp) +
                                   "&pressure=" + String(pres) +

                                   "&longitude=" + String(Longitude) +
                                   "&latitude=" + String(Latitude) +

                                   "&year=" + String(year) +
                                   "&month=" + String(month) +
                                   "&day=" + String(day) +

                                   "&hour=" + String(hour) +
                                   "&minute=" + String(minutes) +
                                   "&second=" + String(seconds);

              Serial.println(httpPayload);

              int result=sendToServer(httpPayload);
              if(result != 200) {
                push();
                Serial.println("http response not as expected. Saving to memory...");
              }  
            }else{
              Serial.println("There is no internet connection. Saving to memory...");
              push();
            }

        }

        delay(10000); // 10 seconds

    }

private:
    bool enoughSTDistance;
} measure_task;



/* loop 2:
 * upload tuples when internet is available
 */
class StoreTask : public Task {
protected:
    /*void setup() {

    }
    */
    void loop() {
      if (WiFi.status() == WL_CONNECTED){
        while(tuplesStored>0){

          String httpPayload=pop();
          int result=sendToServer(httpPayload);
          if (result==200) {
            Serial.println("Uploaded old tuple:");
            Serial.println(httpPayload);
            tuplesStored--;
          }
          yield();
        }

      }else  
          delay(60000); // wait for tuples to build up or wifi connection to be available
    }
} memory_task;


/*
 * -------------- SETUP
 */
void setup()
{
  
  // Debug console
  Serial.begin(9600);
  
  // Debug led
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, LOW); //Set to known state, because we're using a toggle
  
  //GPS Serial
  GPSerial.begin(9600);

  //Check temperature sensor
  if (!bme.begin(0x76) ){
    toggleLed();
    Serial.println("BME sensor unreachable");
    while(1) delay (1000);
    
  } else { //just for racer, print BME sensor specs
    bme_temp->printSensorDetails();
    bme_pressure->printSensorDetails();
    bme_humidity->printSensorDetails();
  }


  //Let's talk to the GPS sensor!
  toggleLed();
  while(GPSerial.available()<=0){
    
    Serial.println("GPS serial unreachable");
    delay(1000);
  }
  toggleLed();

  //setup connection
  WiFi.begin(ssid, pass);

  if(!LittleFS.begin()){
    toggleLed();
    Serial.println("Error mounting");
  }


  tuplesStored=0;
  tupleSize=5*sizeof(float)+6*sizeof(int);

  toggleLed();
  File f=LittleFS.open("/tuples", "w"); //empty file
  f.close();
  toggleLed();

  Scheduler.start(&memory_task);
  Scheduler.start(&measure_task);

  Scheduler.begin();
    
}


/*
 * ------------------ LOOP
 */
void loop()
{
 
}
