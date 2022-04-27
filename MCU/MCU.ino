
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
float Latitude , Longitude;
int year , month , date, hour , minute , second;



//----------- WI-FI SECTION ---------------------------

#include <ESP8266WiFi.h>


char ssid[] = "Brucewillis'";
char pass[] = "quiprende!";


//------------ HTTP SECTION ----------------------------
#include <ESP8266HTTPClient.h>
WiFiClient client;
char serverURL[] = "http://192.168.1.139:80/getSensorValue.php";



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
  Serial.print("Longitude = ");
  Longitude=gps.location.lng();
  year=gps.date.year();
}

//--------------- SEND RESULTS TO SERVER
void sendToServer(String sensorData){
  HTTPClient http; //un oggetto http
  if (WiFi.status() != WL_CONNECTED){
    Serial.println("Ma non c'è internet");
  }else{
    http.begin(client, serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    int httpCode = http.POST(sensorData);
    Serial.print("Server response was ");
    Serial.println(httpCode);
    http.end();
  }
}


void setup()
{
  // Debug console
  Serial.begin(9600);
  Serial.println("Qua ci arrivo");
  
  //GPS Serial
  GPSerial.begin(9600);

  //Check temperature sensor
  if (!bme.begin(0x76) ){
    Serial.println("Fallimento");
    while(1) delay (1000);
  } else { //just for racer
    bme_temp->printSensorDetails();
    bme_pressure->printSensorDetails();
    bme_humidity->printSensorDetails();
  }


  //Let's catch GPS signal!
  while(GPSerial.available()<=0){
    Serial.println("Waiting for gps");
  }

  //setup connection TODO chip will work when offline
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("In attesa di connessione...");
  }
  Serial.println("Connected.");
  
}

void loop()
{
  SensorRead();
  String sensorData="humidity="+String(humi)+"&temperature="+String(temp)+"&pressure="+String(pres)+"&year="+String(year)+"&longitude="+String(Longitude);
  Serial.println(sensorData);
  sendToServer(sensorData);
  delay(60000);  
  
}
