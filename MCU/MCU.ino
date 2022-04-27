
#include <Wire.h>
#include <SPI.h>
#include <Adafruit_BME280.h>

Adafruit_BME280 bme; // use I2C interface
Adafruit_Sensor *bme_temp = bme.getTemperatureSensor();
Adafruit_Sensor *bme_pressure = bme.getPressureSensor();
Adafruit_Sensor *bme_humidity = bme.getHumiditySensor();

float temp, humi, pres;
//int ledPin=2;


// Your WiFi credentials.
// Set password to "" for open networks.

#include <ESP8266WiFi.h>
//#include <HTTPClient.h>
#include <ESP8266HTTPClient.h>
WiFiClient client;

char ssid[] = "Gruppo14";
char pass[] = "12345678";
char serverURL[] = "http://192.168.177.210:80/getSensorValue.php";

void SensorRead() {
  temp=bme.readTemperature();
  humi=bme.readHumidity();
  pres=bme.readPressure()/100.0F;
  Serial.print("temp = ");  // Print the results...
  Serial.println(temp);  // ...to the serial monitor:
}

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
  Serial.begin(115200);

  if (!bme.begin(0x76) ){
    Serial.println("Fallimento");
    while(1) delay (1000);
  } else { //just for racer
    bme_temp->printSensorDetails();
    bme_pressure->printSensorDetails();
    bme_humidity->printSensorDetails();
  }

  //setup connection
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
  String sensorData="humidity="+String(humi)+"&temperature="+String(temp)+"&pressure="+String(pres);
  Serial.println(sensorData);
  sendToServer(sensorData);
  delay(5000);  
  
}
