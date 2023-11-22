#include <WiFi.h>
#include <HTTPClient.h>
#include <time.h>
#include <SPI.h>
#include <MFRC522.h>
#include <LiquidCrystal_I2C.h>
#include <Servo_ESP32.h>

#define SS_PIN 5
#define RST_PIN 4
static const int servoPin = 17;

Servo_ESP32 servo;
MFRC522 mfrc522(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 0);

const char* ssid = "BoisBase@unifi";
const char* password = "Base2022";
const char* device_token = "c95f21ac3e158fe9";
int timezone = 8 * 3600;
int time_dst = 0;
String getData, Link;
String OldCardID = "";
unsigned long previousMillis1 = 0;
unsigned long previousMillis2 = 0;
String URL = "http://192.168.1.105/rfidattendance/getdata.php";
int flag = 0;

void setup() {
  Serial.begin(115200);
  lcd.init();
  lcd.backlight();
  servo.attach(servoPin);
  SPI.begin();
  mfrc522.PCD_Init();
  connectToWiFi();
  configTime(timezone, time_dst, "pool.ntp.org", "time.nist.gov");
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("SCAN CARD");
}

void loop() {
  if (!WiFi.isConnected()) {
    connectToWiFi();
  }
  if (millis() - previousMillis1 >= 1000) {
    previousMillis1 = millis();
    time_t now = time(nullptr);
    struct tm* p_tm = localtime(&now);
    Serial.println(p_tm);
    if (millis() - previousMillis2 >= 15000) {
      previousMillis2 = millis();
      OldCardID = "";
    }
    delay(50);
    if (!mfrc522.PICC_IsNewCardPresent()) {
      return;
    }
    if (!mfrc522.PICC_ReadCardSerial()) {
      return;
    }
    String CardID = "";
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      CardID += mfrc522.uid.uidByte[i];
    }
    if (CardID == OldCardID) {
      return;
    } else {
      OldCardID = CardID;
    }
    SendCardID(CardID);
    delay(1000);
  }
}

void SendCardID(String Card_uid) {
  Serial.println("Sending the Card ID");
  if (WiFi.isConnected()) {
    HTTPClient http;
    getData = "?card_uid=" + String(Card_uid) + "&device_token=" + String(device_token);
    Link = URL + getData;
    http.begin(Link);
    int httpCode = http.GET();
    String payload = http.getString();
    Serial.println(httpCode);
    Serial.println(Card_uid);
    Serial.println(payload);

    if (httpCode == 200) {
      if (payload.substring(0, 5) == "login") {
        String user_name = payload.substring(5);
        lcd.init();
        lcd.backlight();
        lcd.setCursor(0, 0);
        lcd.print("Welcome ");
        lcd.print(user_name);
        Serial.print(F("Welcome "));
        Serial.println(user_name);
        Serial.println("servo open");
        servo.write(90);
        delay(2000);
        servo.write(0);
        delay(2000);
        Serial.println("servo closed");
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SCAN CARD");
      } else if (payload.substring(0, 6) == "logout") {
        String user_name = payload.substring(6);
        lcd.init();
        lcd.backlight();
        lcd.setCursor(0, 0);
        lcd.print("Good Bye ");
        lcd.print(user_name);
        Serial.print(F("Good Bye "));
        Serial.println(user_name);
        Serial.println("servo open");
        servo.write(90);
        delay(2000);
        servo.write(0);
        delay(2000);
        Serial.println("servo closed");
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SCAN CARD");
      } else if (payload == "succesful") {
        Serial.println(F("New Card Detected"));
        lcd.init();
        lcd.backlight();
        lcd.setCursor(0, 0);
        lcd.print("New Card Detected");
        delay(1000);
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SCAN CARD");
      } else if (payload == "available") {
        Serial.println(F("Card Already Register"));
        lcd.init();
        lcd.backlight();
        lcd.setCursor(0, 0);
        lcd.print("Card Already Register");
        delay(1000);
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SCAN CARD");
      }
      else
      {
        Serial.println(F("Card not Register yet"));
        lcd.init();
        lcd.backlight();
        lcd.setCursor(0, 0);
        lcd.print("Card not Register yet");
        delay(1000);
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SCAN CARD");
      }
      delay(100);
      http.end();
    }
  }
}

void connectToWiFi() {
  WiFi.mode(WIFI_OFF);
  delay(1000);
  WiFi.mode(WIFI_STA);
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  delay(1000);
  lcd.clear();
  Serial.println("");
  Serial.println("Connected");
  lcd.setCursor(0, 0);
  lcd.print("Connected..");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  delay(1000);
  lcd.clear();
}