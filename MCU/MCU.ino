
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
SoftwareSerial GPSerial(12,13);
float Latitude , Longitude, oldLatitude, oldLongitude;
int year , month , day, hour , minutes , seconds , oldMinutes, oldSeconds;



//----------- WI-FI SECTION ---------------------------

#include <ESP8266WiFi.h>


char ssid[] = "Gruppo14";
char pass[] = "12345678";


//------------ HTTP SECTION ----------------------------
#include <ESP8266HTTPClient.h>
WiFiClient client;
char serverURL[] = "http://192.168.1.139:80/getSensorValue.php";

bool pendingOperations=false; //<-- are there any tuples saved in memory? To move to FS section


//  FUNCTIONS


//--------------- GET SENSOR READING ---------------------
void SensorRead() {

  //----------------------- CLIMATE
  temp=bme.readTemperature();
  humi=bme.readHumidity();
  pres=bme.readPressure()/100.0F;
  Serial.print("temp = ");  // Print the results...
  Serial.println(temp);  // ...to the serial monitor:


  //----------------------- COORDINATES
  gps.encode(GPSerial.read());
  
  oldLatitude=Latitude;
  oldLongitude=Longitude;
  
  Latitude=gps.location.lat();
  Longitude=gps.location.lng();

  oldMinutes=minutes;
  oldSeconds=seconds;
  
  year=gps.date.year();
  month=gps.date.month();
  day=gps.date.day();
  
  hour=gps.time.hour();
  minutes=gps.time.minute();
  seconds=gps.time.second();

}

//--------------- SEND RESULTS TO SERVER
void sendToServer(String sensorData){
  HTTPClient http; //un oggetto http
    http.begin(client, "http://"+WiFi.gatewayIP().toString()+":80/getSensorValue.php");   // TODO http.begin(client, serverURL); for debug purpose, however, it will connect to phone hotspot and forward http requesto to chroot
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    int httpCode = http.POST(sensorData);
    Serial.print("Server response was ");
    Serial.println(httpCode);
    http.end();
  
}


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
    
}

void loop()
{
  SensorRead();
  String latestSensorData="humidity="+String(humi)+
                          "&temperature="+String(temp)+
                          "&pressure="+String(pres)+
                          
                          "&longitude="+String(Longitude)+
                          "&latitude="+String(Latitude)+

                          "&year="+String(year)+
                          "&month="+String(month)+
                          "&day="+String(day)+
                          
                          "&hour="+String(hour)+
                          "&minute="+String(minutes)+
                          "&second="+String(seconds);
  
  Serial.println(latestSensorData);

  bool enoughSTDistance=true;

  /* Calculate if sqrt(DISTANCE/100 + ELAPSEDSECONDS/300)>=1
   *  that is to say, either the device has travelled 100 meters, or 5 minutes have elapsed, or else check a quadratic combination of those two conditions
   */
  // enoughSTDistance=calculateSTDistance();

  
  if(WiFi.status() == WL_CONNECTED) {
    
    if (enoughSTDistance) sendToServer(latestSensorData);  //TODO check http response so we don't lose tuples
    /*
     * check memory and sendToServer old tuples if any
     * 
     * popAll();
     */
    
  } else {
    Serial.println("There is no internet connection. Saving to memory...");
    /*
     * SAVE TO MEMORY TODO
     * 
     * if (enoughSTDistance) bool esito=push(latestSensorData);  
     * if (esito) ){
     *  //everything's fine
     * } else {
     *  //we're short on memory, We're going to lose this tuple
     * }
     * 
     */
  }

   
  delay(10000); //10 seconds
  
}
