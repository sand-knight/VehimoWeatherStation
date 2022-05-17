#include <Scheduler.h>


#include <Wire.h>
#include <SPI.h>
#include <Adafruit_BME280.h>


//----------  TEMPERATURE SENSOR ---------------------
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
int year , month , day, hour , minutes , seconds , oldMinutes, oldSeconds;
float lastKnownSpeed;


//----------- WI-FI SECTION ---------------------------

#include <ESP8266WiFi.h>


char ssid[] = "Gruppo14";
char pass[] = "12345678";


//------------ HTTP SECTION ----------------------------
#include <ESP8266HTTPClient.h>
WiFiClient client;
char serverURL[] = "http://192.168.1.139:80/getSensorValue.php";

bool pendingOperations=false; //<-- are there any tuples saved in memory? To move to FS section



//--------------------------------------------  FUNCTIONS


/*
 * --------------- GET SENSOR READING
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
  unsigned long t1=micros(); //debug var
  
  while (GPSerial.available() > 0) GPSerial.read();
  
  unsigned long t2=micros(); //debug var
  Serial.println("Flushing time: "+String((t2-t1)/1000)+"ms");


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
  t1=micros(); int i=0, j=0; //debug vars
  
  do{
    j++;
    while (GPSerial.available()<=0) { delay(200); i++; }
  }while (!gps.encode(GPSerial.read()));
  
  t2=micros(); //debug var
  
  Serial.print("Got out of nested whiles in "+String((t2-t1)/1000)+"ms: ");
  Serial.println("gps.enconde was false "+String(j-1)+" times, and waited for characters "+String(i)+" times"); 
  
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
 */
void sendToServer(String sensorData){
  HTTPClient http; //un oggetto http
    http.begin(client, "http://"+WiFi.gatewayIP().toString()+":80/getSensorValueM.php");   // TODO http.begin(client, serverURL); for debug purpose, however, it will connect to phone hotspot and forward http requesto to chroot
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    int httpCode = http.POST(sensorData);
    Serial.print("Server response was ");
    Serial.println(httpCode);
    http.end();
  
}

/*
 * ---------------- CAKCULATE SPEED AND COMPARE space/time DISTANCE TO 1
 */
 bool calculateSTDistance(){
    double distance;
    int elapsedTime=(minutes-oldMinutes)*60+seconds-oldSeconds;
    if (elapsedTime<0) elapsedTime+=3600;

    if(Longitude!=0.00 && Latitude!=0.00){
      distance=gps.distanceBetween(Latitude, Longitude, oldLatitude, oldLongitude);
    } else distance=0;
    
    if(elapsedTime>0){  
      lastKnownSpeed= distance/((double)elapsedTime);
    }
    else lastKnownSpeed=0;
    

    double result=sqrt(distance/100.00+( ((double)elapsedTime))/300.00 );

    String formulaDebug="sqrt("+String(distance)+"/100+"+String(elapsedTime)+"/300) equals "+String(result);
    Serial.println(formulaDebug);
    sendToServer("distance="+formulaDebug);             // DEBUG

   
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


class MeasureTask : public Task
{
protected:
    /*void setup()
    {
     
    }*/

    void loop()
    {
        SensorRead();

        enoughSTDistance = true;

        /* Calculate if sqrt(DISTANCE/100 + ELAPSEDSECONDS/300)>=1
         *  that is to say, either the device has travelled 100 meters, or 5 minutes have elapsed, or else check a quadratic combination of those two conditions
         */
        enoughSTDistance=calculateSTDistance();

        /*
         * Build the http payload. This assignment should be made
         * only we're going to make an http request.
         * However, for testing purposes, we're going to need
         * its output for debug
         *
         * TODO move into "wifi" if statement
         */
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

        /*
         *  If connected, upload data
         *  else save data for later
         */
        if (WiFi.status() == WL_CONNECTED)
        {

            if (enoughSTDistance)
                sendToServer(httpPayload); // TODO check http response so we don't lose tuples

        }else{

            Serial.println("There is no internet connection. Saving to memory...");
            /*
             * TODO SAVE TO MEMORY
             *
             * if (enoughSTDistance) {
             *    bool esito=push();
             *    if (esito) ){
             *       //everything's fine
             *    } else {
             *       //we're short on memory, We're going to lose this tuple
             *    }
             * }
             *
             */
        }

        delay(10000); // 10 seconds
    }

private:
    bool enoughSTDistance;
} measure_task;

class StoreTask : public Task {
protected:
    /*void setup() {

    }
    */
    void loop() {
        //if (uploadable_tuple() && WiFi.status() == WL_CONNECTED)
            //tuple=memoryPop();

            /* String httpPayload = "humidity=" + String(tuple.humi) +
                             "&temperature=" + String(tuple.temp) +
                             "&pressure=" + String(tuple.pres) +

                             "&longitude=" + String(tuple.Longitude) +
                             "&latitude=" + String(tuple.Latitude) +

                             "&year=" + String(tuple.year) +
                             "&month=" + String(tuple.month) +
                             "&day=" + String(tuple.day) +

                             "&hour=" + String(tuple.hour) +
                             "&minute=" + String(tuple.minutes) +
                             "&second=" + String(tuple.seconds);
            */

           //sendToServer(httpPayload)
            sendToServer("memoryop="+String(millis()));             //    DEBUG
            Serial.println("Fake memory operations");
            
            //yield();
        //else
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
  
  
  //GPS Serial
  GPSerial.begin(9600);

  //Check temperature sensor
  if (!bme.begin(0x76) ){
    Serial.println("BME sensor unreachable");
    while(1) delay (1000);
    
  } else { //just for racer, print BME sensor specs
    bme_temp->printSensorDetails();
    bme_pressure->printSensorDetails();
    bme_humidity->printSensorDetails();
  }


  //Let's talk to the GPS sensor!
  while(GPSerial.available()<=0){
    Serial.println("GPS serial unreachable");
    delay(1000);
  }

  //setup connection
  WiFi.begin(ssid, pass);

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
