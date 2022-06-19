#include "LittleFS.h"

float temp, humi, pres;
int year;
uint8_t month , day, hour , minutes , seconds;
float Latitude , Longitude;

int tuplesStored, tupleSize;

void fakeMeasure(){
  temp=random();
  humi=random();
  pres=random();
  year=2022;
  month=4;
  day=10;
  hour=24;
  minutes=37;
  seconds=28;

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
}

void push(){

  uint8_t *toWrite;
  uint8_t buffer[tupleSize];
  byte i, offset=0;

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

  toWrite=(uint8_t*)&month;
  for(i=0; i<sizeof(month); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(month);

  toWrite=(uint8_t*)&day;
  for(i=0; i<sizeof(day); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(day);
  
  toWrite=(uint8_t*)&hour;
  for(i=0; i<sizeof(hour); i++){
    buffer[offset+i]=toWrite[i];
   }
  offset+=sizeof(hour);
  
  toWrite=(uint8_t*)&minutes;
  for(i=0; i<sizeof(minutes); i++){
    buffer[offset+i]=toWrite[i];
  }
  offset+=sizeof(minutes);

  toWrite=(uint8_t*)&seconds;
  for(i=0; i<sizeof(seconds); i++){
    buffer[offset+i]=toWrite[i];
  }
  

  for (i=0;i<tupleSize; i++){
    Serial.print(buffer[i]);
    Serial.print(" ");
  }

  
  File file = LittleFS.open("/tuples", "r+");
  file.seek(tupleSize*tuplesStored, SeekSet);

  file.write(buffer, tupleSize);



  file.close();

  tuplesStored++;
}

String pop(){

  if(tuplesStored==0){
    Serial.println("No tuples to pop");
    return "";
  }

  File file = LittleFS.open("/tuples", "r");
  //uint8_t *toRead;
  uint8_t buffer[tupleSize];
  byte offset=0;
  float* asFloat;
  int* asInt;
  uint8_t* asByte;

  file.seek(tupleSize* (tuplesStored - 1), SeekSet);

  file.read(buffer, tupleSize);

  file.close();


  for (int i=0;i<tupleSize; i++){
    Serial.print(buffer[i]);
    Serial.print(" ");
  }
  Serial.println();


  //Read temperature
    for(int i=0; i<sizeof(float); i++){
    Serial.println(buffer[i]);

  }

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
  payload+="&seconds="+String(*asByte);



  tuplesStored--;
  return payload;

}

void setup() {
  Serial.begin(9600);

  if(!LittleFS.begin()){
    Serial.println("Error mounting");
  }


  tuplesStored=0;
  tupleSize=5*sizeof(float)+6*sizeof(int);

  File f=LittleFS.open("/tuples", "w");
  f.close();
}

void loop() {
  if(Serial.available()>0){
    char input;
    Serial.readBytes(&input, 1);

    if(input=='m'){
      fakeMeasure();
      push();
    }
    else if(input=='l'){
      Serial.println(pop());
    }else
      Serial.println("m to write measure, l to load a measure");
  }
  delay(500);
}
