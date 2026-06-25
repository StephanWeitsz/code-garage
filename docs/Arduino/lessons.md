# Serial Monitor

# Components

## Lesson 15: Using a Photoresistor (Light Sensor)

Key Concepts:

How a photoresistor (LDR) works — resistance drops as light increases.
Building a voltage divider circuit with a 10kΩ resistor.
Calibrating the sensor by printing raw values in different lighting.
Using the sensor to turn an LED on automatically in dark conditions.

```cpp
int lightPin = A0;
int ledPin = 13;
int lightLevel = 0;
int threshold = 300;     // Adjust based on your room lighting

void setup() {
  pinMode(ledPin, OUTPUT);
  Serial.begin(9600);
}

void loop() {
  lightLevel = analogRead(lightPin);
  Serial.println(lightLevel);
  
  if (lightLevel < threshold) {
    digitalWrite(ledPin, HIGH);   // Dark → LED on
  } else {
    digitalWrite(ledPin, LOW);    // Light → LED off
  }
  delay(200);
}
```

Line-by-Line Explanation:

The photoresistor + fixed resistor forms a voltage divider.
Lower lightLevel means darker room.
The threshold value must be tuned by watching Serial Monitor in light vs dark.
LED acts as an automatic night light.

### Homework
Add a second LED (red and green) so the green LED turns on in bright light and red in the dark.

## Lesson 16: Using a Button with Debouncing

Key Concepts:

Why buttons “bounce” and create multiple false triggers.
Simple software debouncing with delay().
Tracking button state changes (pressed vs released).
Using a flag variable to detect only the moment the button is first pressed.

```cpp
int buttonPin = 2;
int ledPin = 13;
int buttonState = 0;
int lastButtonState = 0;
bool ledOn = false;

void setup() {
  pinMode(buttonPin, INPUT);
  pinMode(ledPin, OUTPUT);
  Serial.begin(9600);
}

void loop() {
  buttonState = digitalRead(buttonPin);
  
  if (buttonState != lastButtonState) {     // State changed
    delay(50);                              // Simple debounce
    if (buttonState == HIGH) {              // Button just pressed
      ledOn = !ledOn;                       // Toggle LED state
      digitalWrite(ledPin, ledOn ? HIGH : LOW);
      Serial.println("Button pressed – LED toggled");
    }
  }
  lastButtonState = buttonState;
}
```

Line-by-Line Explanation:

We compare current and previous button states to detect only edges.
delay(50) gives the button time to settle (basic debounce).
ledOn = !ledOn toggles between true/false each press.
The LED stays in its new state until pressed again.

### Homework
Improve the debounce using the millis() timing function instead of delay() so the program doesn’t freeze during the wait.


## Lesson 17: millis() – Non-Blocking Timing

Key Concepts:

Why delay() freezes your program and when to avoid it.
Using millis() for non-blocking timing (great for multitasking).
Tracking elapsed time without pausing the loop.
Building a simple “blink without delay” sketch.

```cpp
int ledPin = 13;
unsigned long previousMillis = 0;
const long interval = 1000;   // 1 second
int ledState = LOW;

void setup() {
  pinMode(ledPin, OUTPUT);
  Serial.begin(9600);
}

void loop() {
  unsigned long currentMillis = millis();
  
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;          // Reset the timer
    if (ledState == LOW) {
      ledState = HIGH;
    } else {
      ledState = LOW;
    }
    digitalWrite(ledPin, ledState);
    Serial.println("LED toggled");
  }
}
```

Line-by-Line Explanation:

unsigned long is used because millis() returns a very large number.
millis() returns milliseconds since the Arduino started.
The if statement checks if enough time has passed without using delay().
The LED toggles independently while the loop keeps running.

### Homework
Add a second LED that blinks at a different rate using another millis() timer.

## Lesson 18: Using Arrays

Key Concepts:

Storing multiple values in one variable using arrays.
Accessing array elements with indexes (starting at 0).
Looping through arrays with for.
Using arrays to control multiple LEDs or store pin numbers.

```cpp
int ledPins[5] = {2, 3, 4, 5, 6};   // Array of 5 pins

void setup() {
  for (int i = 0; i < 5; i++) {
    pinMode(ledPins[i], OUTPUT);
  }
}

void loop() {
  for (int i = 0; i < 5; i++) {
    digitalWrite(ledPins[i], HIGH);
    delay(200);
    digitalWrite(ledPins[i], LOW);
    delay(200);
  }
}
```

Line-by-Line Explanation:

int ledPins[5] = {2,3,4,5,6}; creates an array holding 5 pin numbers.
The first for loop sets all pins as outputs.
The second for loop cycles through the array to blink each LED in sequence.

### Homework
Create an array of delay times and use it to make each LED blink at a different speed.


## Lesson 19: 7-Segment Display (Part 1)

Key Concepts:

How a common-cathode 7-segment display works.
Controlling each segment (a–g) individually.
Binary representation of digits (0–9).
Building a simple counter that displays numbers 0–9.

```cpp
int pins[7] = {2,3,4,5,6,7,8};   // a b c d e f g

// Array of patterns for digits 0-9 (1 = segment on)
byte digits[10] = {
  B0111111, // 0
  B0000110, // 1
  B1011011, // 2
  B1001111, // 3
  B1100110, // 4
  B1101101, // 5
  B1111101, // 6
  B0000111, // 7
  B1111111, // 8
  B1101111  // 9
};

void setup() {
  for (int i = 0; i < 7; i++) {
    pinMode(pins[i], OUTPUT);
  }
}

void loop() {
  for (int digit = 0; digit < 10; digit++) {
    displayDigit(digit);
    delay(800);
  }
}

void displayDigit(int d) {
  byte pattern = digits[d];
  for (int i = 0; i < 7; i++) {
    digitalWrite(pins[i], bitRead(pattern, i));
  }
}
```

Line-by-Line Explanation:

Each bit in the byte pattern represents one segment (a–g).
bitRead() extracts each bit to turn segments on/off.
The custom displayDigit() function makes the main loop cleaner.

### Homework
Modify the sketch to count from 0 to 9 and then back down to 0.

## Lesson 20: 7-Segment Display (Part 2) – Two Digits

Key Concepts:

Multiplexing two 7-segment displays to show two-digit numbers.
Using the millis() technique to refresh displays rapidly.
Avoiding flicker by updating both digits in quick succession.
Displaying numbers from 00 to 99.

```cpp
int digit1Pins[7] = {2,3,4,5,6,7,8};     // First display
int digit2Pins[7] = {9,10,11,12,13,A0,A1}; // Second display
int digit1Cathode = A2;
int digit2Cathode = A3;

byte digits[10] = {B0111111, B0000110, /* ... same patterns as Lesson 18 */ };

unsigned long previousMillis = 0;
int number = 42;   // Number to display

void setup() {
  // Set all segment pins as outputs
  for (int i = 0; i < 7; i++) {
    pinMode(digit1Pins[i], OUTPUT);
    pinMode(digit2Pins[i], OUTPUT);
  }
  pinMode(digit1Cathode, OUTPUT);
  pinMode(digit2Cathode, OUTPUT);
}

void loop() {
  unsigned long currentMillis = millis();
  if (currentMillis - previousMillis >= 5) {   // Refresh every 5ms
    previousMillis = currentMillis;
    showNumber(number);
  }
}

void showNumber(int num) {
  int tens = num / 10;
  int ones = num % 10;
  
  // Display tens digit
  digitalWrite(digit2Cathode, HIGH);   // Turn off other digit
  displayOnPins(digit1Pins, tens);
  digitalWrite(digit1Cathode, LOW);
  delay(5);
  digitalWrite(digit1Cathode, HIGH);   // Turn off
  
  // Display ones digit
  digitalWrite(digit1Cathode, HIGH);
  displayOnPins(digit2Pins, ones);
  digitalWrite(digit2Cathode, LOW);
  delay(5);
  digitalWrite(digit2Cathode, HIGH);
}

void displayOnPins(int pins[], int d) {
  byte pattern = digits[d];
  for (int i = 0; i < 7; i++) {
    digitalWrite(pins[i], bitRead(pattern, i));
  }
}
```

Line-by-Line Explanation:

Multiplexing turns one digit on at a time very quickly so both appear lit.
number / 10 and number % 10 extract tens and ones digits.
The short delay(5) and rapid switching prevent visible flicker.

### Homework
Make the two-digit display count up from 00 to 99 automatically.

## Lesson 21: Using a Shift Register (74HC595)

Key Concepts:

Expanding output pins using the 74HC595 shift register.
Serial-to-parallel conversion (sending 8 bits with only 3 pins).
shiftOut() function.
Controlling 8 LEDs with just 3 Arduino pins.

```cpp
int latchPin = 8;   // ST_CP
int clockPin = 9;   // SH_CP
int dataPin = 10;   // DS

void setup() {
  pinMode(latchPin, OUTPUT);
  pinMode(clockPin, OUTPUT);
  pinMode(dataPin, OUTPUT);
}

void loop() {
  for (int i = 0; i < 256; i++) {   // Count from 0 to 255 in binary
    digitalWrite(latchPin, LOW);
    shiftOut(dataPin, clockPin, MSBFIRST, i);
    digitalWrite(latchPin, HIGH);
    delay(100);
  }
}
```

Line-by-Line Explanation:

latchPin tells the chip when to output the data.
shiftOut() sends 8 bits one by one on the data pin using the clock.
MSBFIRST sends the most significant bit first.
Each number 0–255 lights a unique pattern on the 8 LEDs.

### Homework
Use the shift register to create a “running light” (Knight Rider) effect across the 8 LEDs.

## Lesson 22: The Piezo Buzzer

Key Concepts:

Creating sound with a Piezo buzzer.
Using the tone() and noTone() functions.
Generating different frequencies (Hertz) to make musical notes.
Understanding the relationship between frequency and pitch.

```cpp
int buzzerPin = 8;

void setup() {
  pinMode(buzzerPin, OUTPUT);
}

void loop() {
  tone(buzzerPin, 440);      // Play 'A' note (440Hz)
  delay(500);
  noTone(buzzerPin);         // Stop sound
  delay(500);
}
```

Line-by-Line Explanation:

tone(pin, frequency) generates a square wave at the specified frequency.
noTone(pin) stops the square wave, silencing the buzzer.
440Hz is the standard frequency for the musical note A4.

### Homework
Program the Arduino to play a simple three-note melody (e.g., Do-Re-Mi).

## Lesson 23: Ultrasonic Distance Sensor (HC-SR04)

Key Concepts:

Measuring distance using sound waves (sonar).
Triggering a pulse and measuring the echo duration (pulseIn()).
Calculating distance in centimeters: 
distance=duration × 0.0342 
distance=duration × 0.034 / 2
 
Using pulseIn() to measure the duration of a signal.

```cpp
int trigPin = 9;
int echoPin = 10;
long duration;
int distance;

void setup() {
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);
  Serial.begin(9600);
}

void loop() {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  
  duration = pulseIn(echoPin, HIGH);
  distance = duration * 0.034 / 2;
  
  Serial.print("Distance: ");
  Serial.println(distance);
  delay(100);
}
```

Line-by-Line Explanation:

A 10µs pulse triggers the sensor.
pulseIn() measures the time the echo pin stays HIGH (travel time).
We divide by 2 because the sound traveled to the object and back.

### Homework
Build a "parking sensor" that beeps faster as an object gets closer to the sensor.

## Lesson 24: Servo Motor Control

Key Concepts:

Controlling precise rotation (0–180 degrees) with servos.
Using the <Servo.h> library.
Creating a Servo object and using .attach(), .write(), and .read().
Understanding PWM for positioning.

```cpp
#include <Servo.h>

Servo myServo;

void setup() {
  myServo.attach(9);
}

void loop() {
  myServo.write(0);
  delay(1000);
  myServo.write(90);
  delay(1000);
  myServo.write(180);
  delay(1000);
}
```

Line-by-Line Explanation:

#include <Servo.h> imports the required motor library.
myServo.attach(9) defines the pin where the servo signal is connected.
myServo.write(angle) moves the servo arm to the specified degree.

### Homework
Control the servo position using a potentiometer; as you turn the knob, the servo arm follows the angle.

## Lesson 25: DC Motor Control (Transistors)

Key Concepts:

Why you cannot drive motors directly from Arduino pins (needs external power).
Using a transistor (e.g., TIP120) as a switch.
Protecting the Arduino with a flyback diode.
Controlling motor speed with PWM (analogWrite).

```cpp
int motorPin = 9;

void setup() {
  pinMode(motorPin, OUTPUT);
}

void loop() {
  analogWrite(motorPin, 100);  // Slow
  delay(2000);
  analogWrite(motorPin, 255);  // Fast
  delay(2000);
}
```

Line-by-Line Explanation:

The transistor amplifies the low-current Arduino signal to switch high-current motor power.
analogWrite() varies the voltage effectively changing motor speed.

### Homework
Create a "dimmer" switch for your motor using a potentiometer to control speed from stop to full throttle.

## Lesson 26: The H-Bridge (L293D)
Key Concepts:

Controlling motor direction (forward/reverse), not just speed.
How an H-Bridge circuit works.
Using the L293D chip to drive two DC motors.
Defining multiple control pins to manage state (Forward/Reverse/Stop).
```cpp
int enA = 9;   // Speed control
int in1 = 8;   // Dir control
int in2 = 7;   // Dir control

void setup() {
  pinMode(enA, OUTPUT);
  pinMode(in1, OUTPUT);
  pinMode(in2, OUTPUT);
}

void loop() {
  // Forward
  digitalWrite(in1, HIGH);
  digitalWrite(in2, LOW);
  analogWrite(enA, 200);
  delay(2000);
  
  // Reverse
  digitalWrite(in1, LOW);
  digitalWrite(in2, HIGH);
  analogWrite(enA, 200);
  delay(2000);
}
```

Line-by-Line Explanation:

in1 and in2 determine rotation direction (one must be HIGH, one LOW).
enA controls the motor speed via PWM.
L293D allows changing the polarity across the motor terminals.

### Homework
Build a system where a button toggle reverses the direction of the DC motor.

## Lesson 27: Using an LCD Display (16x2)
Key Concepts:

Interfacing a 16x2 LCD using the LiquidCrystal library.
Pin connections (RS, EN, D4–D7).
Displaying static text and clearing the screen.
Basic cursor control with setCursor().
```cpp
#include <LiquidCrystal.h>

LiquidCrystal lcd(12, 11, 5, 4, 3, 2);  // RS, EN, D4, D5, D6, D7

void setup() {
  lcd.begin(16, 2);                     // 16 columns, 2 rows
  lcd.print("Hello, Arduino!");
}

void loop() {
  lcd.setCursor(0, 1);                  // Move to second row
  lcd.print(millis() / 1000);           // Show seconds since start
  lcd.print(" seconds");
}
```

Line-by-Line Explanation:

`#include <LiquidCrystal.h>` loads the library for easy LCD control.
LiquidCrystal lcd(...) defines the pin connections.
lcd.begin(16,2) initializes the display size.
lcd.print() writes text; setCursor(col,row) positions the cursor.
The loop updates a running timer on the bottom row without clearing the top.

### Homework
Display a custom message on the first row and a live counter on the second.

## Lesson 28: Scrolling Text on LCD
Key Concepts:

Using scrollDisplayLeft() and scrollDisplayRight() for moving text.
Creating long messages that exceed the 16-character width.
Timing delays to control scroll speed.
Combining static and scrolling elements.
```cpp
#include <LiquidCrystal.h>

LiquidCrystal lcd(12, 11, 5, 4, 3, 2);

void setup() {
  lcd.begin(16, 2);
  lcd.print("Arduino LCD Scroll Demo");
}

void loop() {
  for (int i = 0; i < 20; i++) {        // Scroll left 20 positions
    lcd.scrollDisplayLeft();
    delay(300);
  }
  for (int i = 0; i < 20; i++) {        // Scroll back right
    lcd.scrollDisplayRight();
    delay(300);
  }
}
```

Line-by-Line Explanation:

The long initial string is wider than the screen, so scrolling reveals the rest.
Two separate for loops create a back-and-forth marquee effect.
delay(300) sets a readable scroll speed.

### Homework
Make the top row static ("Temperature:") and scroll a changing sensor value on the bottom row.

## Lesson 29: Custom Characters on LCD
Key Concepts:

Creating up to 8 custom 5x8 pixel characters with createChar().
Using byte arrays to define pixel patterns.
Displaying custom symbols like hearts, smileys, or bar graphs.

```cpp
#include <LiquidCrystal.h>

LiquidCrystal lcd(12, 11, 5, 4, 3, 2);

byte heart[8] = {
  B00000,
  B01010,
  B11111,
  B11111,
  B11111,
  B01110,
  B00100,
  B00000
};

void setup() {
  lcd.begin(16, 2);
  lcd.createChar(0, heart);             // Store as character 0
  lcd.print("I ");
  lcd.write(byte(0));                   // Display custom heart
  lcd.print(" Arduino!");
}

void loop() {}
```

Line-by-Line Explanation:

Each B00000 line represents one row of 5 pixels (1 = on).
lcd.createChar(0, heart) loads the pattern into slot 0.
lcd.write(byte(0)) prints the custom character.
### Homework
Create a custom "smiley face" and a "battery" icon, then display both on the LCD.

## Lesson 30: Reading Temperature with LM35 Sensor

Key Concepts:

Using the LM35 analog temperature sensor (10mV per °C).
Converting analogRead values to Celsius and Fahrenheit.
Displaying live temperature on Serial Monitor and/or LCD.
Basic calibration and averaging multiple readings for stability.

```cpp
int sensorPin = A0;
float temperatureC = 0;

void setup() {
  Serial.begin(9600);
}

void loop() {
  int reading = analogRead(sensorPin);
  float voltage = reading * 5.0 / 1024.0;   // Convert to volts
  temperatureC = voltage * 100.0;           // LM35: 10mV/°C → 100 * V
  Serial.print("Temperature: ");
  Serial.print(temperatureC);
  Serial.println(" °C");
  delay(1000);
}
```

Line-by-Line Explanation:

analogRead() gives 0–1023; multiplied by 5.0/1024.0 yields voltage.
For LM35, multiply voltage by 100 to get °C directly.
The loop reads and prints every second.

### Homework
Combine this with the LCD from Lesson 26 to show live temperature on the display.

## Lesson 31: Using a DHT11 Temperature & Humidity Sensor

Key Concepts:

Installing and using the DHT library for digital sensors.
Reading both temperature and relative humidity.
Handling the one-wire digital protocol.
Error checking with DHT.read().

```cpp
#include <DHT.h>

#define DHTPIN 2
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(9600);
  dht.begin();
}

void loop() {
  float h = dht.readHumidity();
  float t = dht.readTemperature();
  
  if (isnan(h) || isnan(t)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }
  
  Serial.print("Humidity: ");
  Serial.print(h);
  Serial.print(" %  Temperature: ");
  Serial.print(t);
  Serial.println(" °C");
  delay(2000);
}
```

Line-by-Line Explanation:

#define sets the pin and sensor type (DHT11 or DHT22).
`dht.begin()` initializes communication.
`readHumidity()` and `readTemperature()` return float values.
`isnan()` checks for invalid readings (common with cheap sensors).

### Homework
Display both humidity and temperature on a 16x2 LCD, updating every 2 seconds.

## Lesson 32: SD Card Reader/Writer

Key Concepts:

Reading and writing data to a microSD card using the SD library.
Storing sensor data or logging over time.
File creation, writing, appending, and reading.
SPI communication for the SD module.

```cpp
#include <SD.h>
#include <SPI.h>

const int chipSelect = 4;  // CS pin for SD card

void setup() {
  Serial.begin(9600);
  Serial.print("Initializing SD card... ");
  if (!SD.begin(chipSelect)) {
    Serial.println("Card failed or not present");
    return;
  }
  Serial.println("Card initialized.");
  
  File dataFile = SD.open("datalog.txt", FILE_WRITE);
  if (dataFile) {
    dataFile.println("Arduino SD Card Test - Lesson 31");
    dataFile.close();
    Serial.println("Data written successfully.");
  } else {
    Serial.println("Error opening file.");
  }
}

void loop() {}
```

Line-by-Line Explanation:

`SD.begin(chipSelect)` initializes the SD card on the specified pin.
`SD.open(filename, FILE_WRITE)` opens (or creates) a file.
`println()` writes a line of text; close() saves and releases the file.
Always check if the file opened successfully to avoid errors.

### Homework: Log temperature or distance sensor readings every 10 seconds to a CSV file on the SD card.

## Lesson 33: IR Remote Control

Key Concepts:

Receiving and decoding signals from an infrared remote.
Using the IRremote library.
Mapping button presses to specific actions (e.g., turn on LED, change servo position).
Learning HEX codes for each remote button.

```cpp
#include <IRremote.h>

int RECV_PIN = 11;
IRrecv irrecv(RECV_PIN);
decode_results results;

void setup() {
  Serial.begin(9600);
  irrecv.enableIRIn();  // Start the receiver
}

void loop() {
  if (irrecv.decode(&results)) {
    Serial.println(results.value, HEX);  // Print button code in HEX
    irrecv.resume();  // Receive the next value
  }
}
```

Line-by-Line Explanation:

IRrecv irrecv(RECV_PIN) creates the receiver object on the specified pin.
`enableIRIn()` starts listening for IR signals.
decode(&results) checks for a received signal; results.value gives the HEX code.
`resume()` prepares for the next button press.

### Homework
Use specific button codes to control an LED or servo (e.g., power button toggles an LED).

## Lesson 34: Stepper Motors (Part 1)

Key Concepts:

Controlling a stepper motor for precise angular movement.
Using the built-in Stepper library.
Understanding steps per revolution and speed control.
Basic forward and backward rotation.

```cpp
#include <Stepper.h>

const int stepsPerRevolution = 2048;  // For 28BYJ-48 with ULN2003
Stepper myStepper(stepsPerRevolution, 8, 9, 10, 11);

void setup() {
  myStepper.setSpeed(10);  // RPM
}

void loop() {
  myStepper.step(stepsPerRevolution);    // One full revolution forward
  delay(1000);
  myStepper.step(-stepsPerRevolution);   // One full revolution backward
  delay(1000);
}
```

Line-by-Line Explanation:

Stepper(steps, pin1, pin2, pin3, pin4) defines the motor and its control pins.
`setSpeed(RPM)` sets rotation speed.
Positive `step()` moves clockwise; negative moves counterclockwise.

### Homework
Make the stepper motor rotate to specific angles (e.g., 90°, 180°) using calculated steps.

## Lesson 35: Stepper Motors (Part 2) – Using a Driver

Key Concepts:

Driving higher-current stepper motors with a driver board (e.g., A4988 or DRV8825).
Using STEP and DIR pins for simpler control.
Microstepping for smoother, quieter motion.
Avoiding the limitations of the basic Stepper library.

```cpp
#define STEP_PIN 2
#define DIR_PIN 3
int steps = 200;  // Steps per revolution (adjust for microstepping)

void setup() {
  pinMode(STEP_PIN, OUTPUT);
  pinMode(DIR_PIN, OUTPUT);
}

void loop() {
  digitalWrite(DIR_PIN, HIGH);  // Set direction
  for (int i = 0; i < steps; i++) {
    digitalWrite(STEP_PIN, HIGH);
    delayMicroseconds(1000);
    digitalWrite(STEP_PIN, LOW);
    delayMicroseconds(1000);
  }
  delay(1000);
}
```

Line-by-Line Explanation:

DIR_PIN sets rotation direction (HIGH/LOW).
Each pulse on STEP_PIN advances the motor one step.
Delay between pulses controls speed (shorter = faster).

### Homework
Add a potentiometer to control speed and a button to change direction.

## Lesson 36: Using the AccelStepper Library

Key Concepts:

Non-blocking stepper control with acceleration and deceleration.
Installing and using the AccelStepper library.
Smooth starts/stops for realistic motion.
Running multiple motors simultaneously.

```cpp
#include <AccelStepper.h>

AccelStepper stepper(AccelStepper::DRIVER, 2, 3);  // STEP, DIR pins

void setup() {
  stepper.setMaxSpeed(1000);
  stepper.setAcceleration(200);
  stepper.moveTo(2000);  // Target position in steps
}

void loop() {
  if (stepper.distanceToGo() == 0) {
    stepper.moveTo(-stepper.currentPosition());  // Reverse direction
  }
  stepper.run();  // Must call this frequently
}
```

Line-by-Line Explanation:

AccelStepper::DRIVER mode uses STEP/DIR pins (common with driver boards).
`setMaxSpeed()` and `setAcceleration()` create smooth motion profiles.
`run()` must be called often in loop() to update the motor.

### Homework
Control two stepper motors at once with different speeds and acceleration profiles.

## Lesson 37: Using the EEPROM Library

Key Concepts:

Storing data permanently even after power is removed (non-volatile memory).
Reading and writing bytes, integers, or floats with the built-in EEPROM library.
Understanding memory limits (typically 1KB on Uno).
Practical uses like saving calibration values or high scores.

```cpp
#include <EEPROM.h>

int address = 0;
int valueToWrite = 123;
int valueRead;

void setup() {
  Serial.begin(9600);
  EEPROM.write(address, valueToWrite);        // Write a byte
  valueRead = EEPROM.read(address);           // Read it back
  Serial.print("Value read from EEPROM: ");
  Serial.println(valueRead);
}

void loop() {}
```

Line-by-Line Explanation:

EEPROM.write(address, value) stores a single byte at the given address.
EEPROM.read(address) retrieves the byte.
Data survives resets and power cycles. For larger values use EEPROM.put() and EEPROM.get() (shown in later lessons).

### Homework
Save a sensor calibration value to EEPROM and load it automatically on startup.

## Lesson 38: Reading and Writing Larger Data with EEPROM.put() and .get()

Key Concepts:

Using `.put()` and `.get()` to store multi-byte data types (ints, floats, structs).
Automatic address calculation to avoid overwriting.
Updating values without unnecessary writes to extend EEPROM life.

```cpp
#include <EEPROM.h>

struct Settings {
  float calibration;
  int threshold;
};

void setup() {
  Serial.begin(9600);
  Settings mySettings = {42.5, 75};
  EEPROM.put(0, mySettings);                  // Write entire struct
  
  Settings loaded;
  EEPROM.get(0, loaded);
  Serial.print("Calibration: "); Serial.println(loaded.calibration);
  Serial.print("Threshold: "); Serial.println(loaded.threshold);
}

void loop() {}
```

Line-by-Line Explanation:

A struct groups related variables.
`EEPROM.put(address, data)` writes any data type safely.
`EEPROM.get(address, variable)` reads it back into the variable.

### Homework
Create a settings menu that saves user preferences (brightness, sensitivity) to EEPROM.

## Lesson 39: Using the Arduino as a Web Server

Key Concepts:

Connecting the Arduino to a network using the Ethernet Shield.
Serving a simple HTML web page.
Reading analog sensors and displaying live values in a browser.
Basic HTTP request handling.
```cpp
#include <SPI.h>
#include <Ethernet.h>

byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress ip(192, 168, 1, 177);
EthernetServer server(80);

void setup() {
  Ethernet.begin(mac, ip);
  server.begin();
}

void loop() {
  EthernetClient client = server.available();
  if (client) {
    client.println("HTTP/1.1 200 OK");
    client.println("Content-Type: text/html");
    client.println();
    client.println("<h1>Arduino Web Server</h1>");
    client.print("Analog 0: ");
    client.println(analogRead(A0));
    client.stop();
  }
}
```

Line-by-Line Explanation:

Ethernet.begin() configures the network.
`server.available()` waits for browser connections.
The code sends a minimal HTML response with live sensor data.

### Homework
Add multiple sensor readings and refresh the page automatically with meta tags.

## Lesson 40: Controlling Arduino from a Web Browser (Web Client)

Key Concepts:

Sending commands from a web page to the Arduino (e.g., turn LEDs on/off).
Parsing simple GET requests.
Creating HTML buttons that trigger Arduino actions.

```cpp
#include <SPI.h>
#include <Ethernet.h>

byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress ip(192, 168, 1, 177);
EthernetServer server(80);
int ledPin = 8;

void setup() {
  pinMode(ledPin, OUTPUT);
  Ethernet.begin(mac, ip);
  server.begin();
}

void loop() {
  EthernetClient client = server.available();
  if (client) {
    String request = client.readStringUntil('\r');
    if (request.indexOf("GET /LED=ON") != -1) digitalWrite(ledPin, HIGH);
    if (request.indexOf("GET /LED=OFF") != -1) digitalWrite(ledPin, LOW);
    
    client.println("HTTP/1.1 200 OK");
    client.println("Content-Type: text/html");
    client.println();
    client.println("<a href=\"/LED=ON\">ON</a><br>");
    client.println("<a href=\"/LED=OFF\">OFF</a>");
    client.stop();
  }
}
```

Line-by-Line Explanation:

The browser sends a URL with parameters (/LED=ON).
`indexOf()` checks the request string for commands.
Simple links act as buttons to control the hardware.

### Homework
Add more controls (brightness via PWM, servo position) through additional links.

## Lesson 41: Introduction to WiFi with ESP8266

Key Concepts:

Using the ESP8266 WiFi module (or ESP-01) with Arduino.
Connecting to your home WiFi network.
Sending data to the Serial Monitor or a web service.
AT commands or the WiFiEsp library for communication.

```cpp
#include <WiFiEsp.h>

char ssid[] = "YourNetwork";
char pass[] = "YourPassword";
int status = WL_IDLE_STATUS;

void setup() {
  Serial.begin(115200);
  WiFi.init(&Serial1);                    // ESP8266 usually on Serial1
  while (status != WL_CONNECTED) {
    status = WiFi.begin(ssid, pass);
    delay(5000);
  }
  Serial.println("Connected to WiFi!");
}

void loop() {}
```

Line-by-Line Explanation:

`WiFi.init()` starts communication with the ESP8266.
`WiFi.begin(ssid, pass)` attempts to join the network.
Once connected, you can use the module as a web client or server.

### Homework
Send temperature or sensor data from the Arduino to a free online IoT service like ThingSpeak.

## Lesson 42: Connecting Arduino to the Internet with ESP8266

Key Concepts:

Using the ESP8266 as a WiFi module to connect the Arduino to a network.
Sending sensor data to online services like ThingSpeak.
Parsing HTTP responses and handling basic error checking.
Setting up the ESP8266 in station mode.

```cpp
#include <SoftwareSerial.h>
SoftwareSerial esp8266(2, 3);  // RX, TX pins

void setup() {
  Serial.begin(9600);
  esp8266.begin(9600);
  sendCommand("AT+RST", 2000);           // Reset the module
  sendCommand("AT+CWMODE=1", 1000);      // Station mode
  sendCommand("AT+CWJAP=\"YourSSID\",\"YourPassword\"", 5000);  // Join network
}

void loop() {
  // Example: Send temperature to ThingSpeak
  String data = "GET /update?api_key=YOURAPIKEY&field1=25 HTTP/1.1";
  sendCommand("AT+CIPSTART=\"TCP\",\"api.thingspeak.com\",80", 2000);
  sendCommand("AT+CIPSEND=" + String(data.length() + 2), 1000);
  esp8266.println(data);
}

String sendCommand(String command, int timeout) {
  String response = "";
  esp8266.println(command);
  long int time = millis();
  while ((time + timeout) > millis()) {
    while (esp8266.available()) response += (char)esp8266.read();
  }
  Serial.print(response);
  return response;
}
```

Line-by-Line Explanation:

SoftwareSerial communicates with the ESP8266 on separate pins.
AT commands configure the module (reset, mode, join WiFi).
CIPSTART and CIPSEND open a TCP connection to send data to a server.
The custom sendCommand function waits for responses.

### Homework
Modify the code to send live DHT11 temperature and humidity data to ThingSpeak every 15 seconds.

# Extra

## Lesson 43: Creating a Simple Web Server with ESP8266

Key Concepts:

Turning the ESP8266 into an access point or web server.
Serving HTML pages directly from the Arduino/ESP combination.
Controlling pins (LEDs, relays) via a browser interface.
Basic CSS for a cleaner web UI.

```cpp
#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>

const char* ssid = "ArduinoServer";
const char* password = "12345678";
ESP8266WebServer server(80);

void setup() {
  pinMode(2, OUTPUT);
  WiFi.softAP(ssid, password);
  server.on("/", handleRoot);
  server.on("/LEDON", handleLEDON);
  server.begin();
}

void loop() { server.handleClient(); }

void handleRoot() {
  server.send(200, "text/html", "<h1>ESP8266 Server</h1><a href=\"/LEDON\">Turn LED ON</a>");
}

void handleLEDON() {
  digitalWrite(2, HIGH);
  server.send(200, "text/html", "LED is ON!");
}
```

Line-by-Line Explanation:

`WiFi.softAP()` creates a WiFi network.
server.on(path, function) registers handlers for different URLs.
`server.send()` returns HTML or text to the browser.
`handleClient()` must run continuously to process requests.

### Homework
Add buttons to turn the LED off and display live sensor readings on the web page.

## Lesson 44: Using MQTT with ESP8266 for IoT

Key Concepts:

Implementing the MQTT protocol for lightweight IoT messaging.
Publishing sensor data and subscribing to control commands.
Connecting to free brokers like Mosquitto or Adafruit IO.
Handling callbacks for incoming messages.

```cpp
#include <ESP8266WiFi.h>
#include <PubSubClient.h>

WiFiClient espClient;
PubSubClient client(espClient);
const char* mqttServer = "broker.mqtt-dashboard.com";

void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived ["); Serial.print(topic); Serial.print("] ");
  for (int i = 0; i < length; i++) Serial.print((char)payload[i]);
  Serial.println();
}

void setup() {
  Serial.begin(9600);
  WiFi.begin("YourSSID", "YourPassword");
  while (WiFi.status() != WL_CONNECTED) delay(500);
  client.setServer(mqttServer, 1883);
  client.setCallback(callback);
  client.connect("ArduinoClient");
  client.subscribe("home/led");
}

void loop() {
  client.loop();
  client.publish("home/temp", "23.5");
  delay(5000);
}
```

Line-by-Line Explanation:

PubSubClient library handles MQTT connections.
`callback()` processes messages received on subscribed topics.
`publish()` sends data; `subscribe()` listens for commands.

### Homework
Control an LED remotely by publishing "ON" or "OFF" to the subscribed topic from another device.

## Lesson 45: Introduction to Node-RED for IoT Dashboards

Key Concepts:

Installing and using Node-RED on a Raspberry Pi or computer.
Creating visual flows to receive MQTT data from Arduino/ESP.
Building simple dashboards with charts and gauges.
Connecting flows to email alerts or database storage.

Line-by-Line Explanation:

This lesson shifts to the Node-RED visual programming tool. You drag nodes (MQTT input, function, dashboard UI) and wire them together. The Arduino/ESP publishes data via MQTT, and Node-RED displays it in a browser dashboard.

### Homework
Create a Node-RED dashboard showing temperature, humidity, and a control button that sends MQTT commands back to your Arduino.

## Lesson 46: Final Project – Smart Home IoT System

Key Concepts:

Combining everything learned: sensors, displays, WiFi, MQTT, web interfaces.
Building a complete system (e.g., temperature monitor with remote control and logging).
Best practices for reliable IoT projects (error handling, reconnect logic).
Expanding to multiple nodes.

```cpp
// Composite code example — full project would combine prior lessons
#include <DHT.h>
#include <ESP8266WiFi.h>
#include <PubSubClient.h>

DHT dht(4, DHT11);
WiFiClient espClient;
PubSubClient client(espClient);

void setup() {
  dht.begin();
  WiFi.begin("SSID", "PASS");
  client.setServer("broker.example.com", 1883);
}

void loop() {
  float t = dht.readTemperature();
  client.publish("home/temp", String(t).c_str());
  delay(10000);
}
```

Line-by-Line Explanation:
This lesson integrates DHT reading, WiFi connection, and MQTT publishing into one sketch. Add error checking (reconnect() function), LCD output, and a Node-RED dashboard for a complete smart sensor node.

### Homework
Build and document your own final project (e.g., a weather station that logs to SD, displays on LCD, and sends alerts via MQTT).

## Lesson 47: Using Interrupts for Responsive Projects

Key Concepts:

Hardware interrupts to handle time-sensitive events without polling.
Attaching interrupts to pins for rising/falling edges.
Debouncing buttons and improving responsiveness in multi-task projects.
Understanding volatile variables and interrupt service routines (ISRs).

```cpp
volatile int interruptCount = 0;
int pin = 2;

void setup() {
  Serial.begin(9600);
  pinMode(pin, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(pin), isr, FALLING);
}

void loop() {
  Serial.print("Interrupts: ");
  Serial.println(interruptCount);
  delay(1000);
}

void isr() {
  interruptCount++;
}
```

Line-by-Line Explanation:

volatile ensures the variable is updated correctly across interrupt and main code.
`attachInterrupt()` links a pin to a function (ISR) on a specific edge (FALLING).
The ISR runs immediately on trigger; keep it short to avoid blocking.
`digitalPinToInterrupt()` converts pin number for compatibility.

### Homework
Use an interrupt to count button presses accurately while blinking an LED in the main loop.

## Lesson 48: Advanced Temperature and Humidity with DHT22

Key Concepts:

Upgrading from DHT11 to DHT22 for better accuracy and range.
Reading floating-point values and handling checksum errors.
Comparing sensors and integrating with displays or logging.
Using the DHT library for simplified code.

```cpp
#include <DHT.h>
#define DHTPIN 7
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(9600);
  dht.begin();
}

void loop() {
  float h = dht.readHumidity();
  float t = dht.readTemperature();
  if (isnan(h) || isnan(t)) {
    Serial.println("Failed to read sensor!");
    return;
  }
  Serial.print("Humidity: "); Serial.print(h); Serial.print(" %\t");
  Serial.print("Temperature: "); Serial.print(t); Serial.println(" *C");
  delay(2000);
}
```

Line-by-Line Explanation:

`DHT()` initializes the sensor type and pin.
`readHumidity()` and `readTemperature()` return floats; check for NaN errors.
Delay prevents too-frequent reads (DHT22 needs ~2 seconds between samples).

### Homework
Log DHT22 readings to an SD card (from Lesson 31) with timestamps.

## Lesson 49: Ultrasonic Distance Sensor Enhancements (HC-SR04)

Key Concepts:

Improving accuracy with temperature compensation and averaging.
Using `pulseIn()` for precise timing.
Creating a simple rangefinder with LED or buzzer feedback.
Filtering noise for reliable measurements.

```cpp
const int trigPin = 9;
const int echoPin = 10;
long duration;
int distance;

void setup() {
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);
  Serial.begin(9600);
}

void loop() {
  digitalWrite(trigPin, LOW); delayMicroseconds(2);
  digitalWrite(trigPin, HIGH); delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  duration = pulseIn(echoPin, HIGH);
  distance = duration * 0.034 / 2;  // Speed of sound in cm/us
  Serial.print("Distance: "); Serial.print(distance); Serial.println(" cm");
  delay(500);
}
```

Line-by-Line Explanation:

Trigger pulse sends ultrasonic burst.
`pulseIn()` measures echo return time.
Formula converts time to distance (adjust for temperature if needed).
Short delay avoids overlapping pulses.

### Homework: Add an LED bar or buzzer that changes based on distance thresholds (e.g., proximity alarm).

## Lesson 50: Wireless Communication with NRF24L01 Modules

Key Concepts:

Point-to-point radio communication using NRF24L01 transceivers.
Sending and receiving data packets between two Arduinos.
Configuring pipes, power levels, and data rates.
Building simple wireless sensor networks.

```cpp
#include <SPI.h>
#include <nRF24L01.h>
#include <RF24.h>
RF24 radio(7, 8);  // CE, CSN pins
const byte address[6] = "00001";

void setup() {
  Serial.begin(9600);
  radio.begin();
  radio.openWritingPipe(address);
  radio.setPALevel(RF24_PA_MIN);
  radio.stopListening();
}

void loop() {
  const char text[] = "Hello World";
  radio.write(&text, sizeof(text));
  delay(1000);
}
```
Line-by-Line Explanation:

RF24() sets up the module with control pins.
`openWritingPipe()` defines the communication address.
`write()` sends data; use a matching receiver sketch on the other Arduino.
Lower PA level for short-range testing.

### Homework
Send temperature data wirelessly from one Arduino to another that displays it on an LCD.

## Lesson 51: Final Capstone Project – Wireless Weather Station

Key Concepts:

Integrating multiple prior lessons into one project (sensors, logging, wireless, display).
Error handling, power management, and modular code design.
Expanding to multi-node networks.
Documenting and troubleshooting a complete system.

```cpp
// Capstone sketch combines DHT22, SD logging, NRF24L01, and LCD
// (Full code would be 100+ lines; build incrementally from prior lessons)
#include <DHT.h>
#include <SD.h>
#include <RF24.h>
// ... (add libraries and setup as needed)

void setup() {
  // Initialize all components
}

void loop() {
  // Read sensors, log to SD, transmit wirelessly, update display
}
```

Line-by-Line Explanation:
This lesson ties everything together. Start with separate modules (sensor reading, logging, transmission), then combine in a main sketch with functions for each task. Add reconnect logic for wireless and error checks for SD.

### Homework
Build, test, and document your own wireless weather station. Share photos or results in the comments!

## Lesson 52: Using the HC-SR04 Ultrasonic Sensor for Echolocation

Key Concepts:

Precise distance measurement using sound wave reflection.
Integrating with the R4's improved processing for faster reads.
Temperature compensation for accuracy.
Applications in robotics or obstacle avoidance.

```cpp
const int trigPin = 9;
const int echoPin = 10;
long duration;
float distance;

void setup() {
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);
  Serial.begin(9600);
}

void loop() {
  digitalWrite(trigPin, LOW); delayMicroseconds(2);
  digitalWrite(trigPin, HIGH); delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  duration = pulseIn(echoPin, HIGH);
  distance = duration * 0.0343 / 2;  // Adjusted for cm
  Serial.print("Distance: "); Serial.print(distance); Serial.println(" cm");
  delay(500);
}
```

Line-by-Line Explanation:
The trigger sends a 10μs pulse; pulseIn() measures the echo time. The formula uses the speed of sound (adjusted slightly for typical room temp). Output to Serial for monitoring.

### Homework
Add an LED that blinks faster as distance decreases (proximity alert).

## Lesson 53: Integrating PIR Motion Sensors with the Arduino Uno R4

Key Concepts:

Detecting infrared changes for motion (warm bodies).
Reducing false triggers with sensitivity adjustments.
Combining with WiFi to send alerts.
Ideal for security or automation projects.

```cpp
int pirPin = 2;
int ledPin = 13;
int pirState = LOW;

void setup() {
  pinMode(pirPin, INPUT);
  pinMode(ledPin, OUTPUT);
  Serial.begin(9600);
}

void loop() {
  int val = digitalRead(pirPin);
  if (val == HIGH) {
    digitalWrite(ledPin, HIGH);
    if (pirState == LOW) {
      Serial.println("Motion detected!");
      pirState = HIGH;
    }
  } else {
    digitalWrite(ledPin, LOW);
    if (pirState == HIGH) {
      Serial.println("Motion ended.");
      pirState = LOW;
    }
  }
  delay(100);
}
```

Line-by-Line Explanation:

`digitalRead()` checks the PIR output. State tracking prevents repeated messages. LED activates on detection; extend with WiFi for remote notifications.

### Homework: Log motion events with timestamps to an SD card or cloud via WiFi.

## Lesson 54: Advanced Arrays and Data Handling on the R4
Key Concepts:

Using arrays for storing multiple sensor readings.
Dynamic data processing on the R4's faster MCU.
Averaging/filtering for smoother results.
Preparing data for wireless transmission.

```cpp
int readings[10];
int index = 0;
int total = 0;

void setup() {
  Serial.begin(9600);
}

void loop() {
  int sensorValue = analogRead(A0);
  total = total - readings[index];
  readings[index] = sensorValue;
  total = total + readings[index];
  index = (index + 1) % 10;
  float average = total / 10.0;
  Serial.print("Average: "); Serial.println(average);
  delay(200);
}
```

Line-by-Line Explanation:

A circular buffer stores the last 10 readings. Subtract old value, add new, then compute average. This smooths noisy analog inputs efficiently.


### Homework
Use an array to store and display multiple ultrasonic distances on the R4's built-in LED matrix.

## Lesson 55: Basic WiFi Setup and Client Connections on Uno R4

Key Concepts:

Leveraging the R4's onboard WiFi module.
Connecting to networks and making HTTP requests.
Sending sensor data to services like ThingSpeak.
Error handling for connection drops.

```cpp
#include <WiFiS3.h>

char ssid[] = "YourNetwork";
char pass[] = "YourPassword";

void setup() {
  Serial.begin(9600);
  while (WiFi.status() != WL_CONNECTED) {
    WiFi.begin(ssid, pass);
    delay(5000);
  }
  Serial.println("Connected!");
}

void loop() {
  // Add sensor send logic here
  delay(10000);
}
```

Line-by-Line Explanation:

WiFiS3 library (specific to R4) handles connection. Loop checks status; once connected, add code to publish data. Reconnect logic is key for reliability.

### Homework
Send temperature data from a DHT sensor to a web server.

## Lesson 56: Controlling Arduino Projects with a Graphical User Interface over WiFi

Key Concepts:

Building a PyQt5 desktop GUI for remote control.
Bidirectional communication (send commands, receive data).
Creating buttons/sliders for LED, servo, or relay control.
Full IoT dashboard example.
```python
# Python GUI side (PyQt5 example - Arduino side uses WiFi server)
import sys
from PyQt5.QtWidgets import QApplication, QWidget, QPushButton

class App(QWidget):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Arduino Control")
        btn = QPushButton("Toggle LED", self)
        btn.clicked.connect(self.sendCommand)
        self.show()

    def sendCommand(self):
        print("Sending ON/OFF command over WiFi...")  # Replace with socket code

if __name__ == '__main__':
    app = QApplication(sys.argv)
    ex = App()
    sys.exit(app.exec_())
```

Line-by-Line Explanation:

This is the desktop side; Arduino runs a server listening for commands. Button triggers socket send. Expand with labels for live sensor feedback.

### Homework
Create a full GUI that controls an LED and displays real-time ultrasonic distance.

## Lesson 56: Advanced Data Visualization with ThingSpeak

Key Concepts:

Sending multiple sensor data points (temp, humidity, light, distance) to a cloud channel.
Configuring ThingSpeak widgets for real-time visualization.
Managing data rates to stay within free-tier limits.
Setting up alerts based on threshold triggers.

```cpp
// Utilize WiFiS3 and HttpClient libraries
// Send data in comma-separated format or JSON
String data = "field1=" + String(temp) + "&field2=" + String(dist);
client.print("POST /update HTTP/1.1\n");
client.print("Host: api.thingspeak.com\n");
client.print("X-THINGSPEAKAPIKEY: YOUR_KEY\n");
// ... finish request headers
```

Line-by-Line Explanation:

Uses POST requests to update multiple fields simultaneously. The API key authenticates the device. Efficient HTTP handling prevents buffer overflows on the R4.

### Homework
Create a ThingSpeak dashboard that tracks temperature and motion frequency, with an email alert if temperature exceeds $35^\circ\text{C}$.

## Lesson 57: Creating a Web-Based Control Dashboard

Key Concepts:

Hosting an HTML/CSS/JavaScript control page directly on the R4.
Using AJAX or Fetch API for near-instant response without page refreshes.
Styling with CSS for mobile-friendly interfaces.
Handling concurrent requests for multi-device interaction.

```cpp
// HTML inside Arduino code
client.println("<h1>Smart Home Control</h1>");
client.println("<button onclick=\"fetch('/LEDON')\">LED ON</button>");
// Server side:
server.on("/LEDON", []() { digitalWrite(ledPin, HIGH); });
```

Line-by-Line Explanation:

The R4 acts as a standalone mini-server. fetch() sends background requests to the Arduino, allowing the UI to remain active while commands are executed.

### Homework
Develop a dark-mode dashboard that displays current sensor data and toggles three different output devices (LEDs or Relays).

## Lesson 58: Introduction to NTP (Network Time Protocol)

Key Concepts:

Synchronizing the Arduino’s internal clock with global NTP servers.
Formatting timestamps for data logging and scheduling.
Accounting for time zones and Daylight Savings Time (DST).
Scheduling tasks based on real-world time.

```cpp
#include <WiFiUdp.h>
#include <NTPClient.h>

WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", offsetSeconds);

void setup() {
  timeClient.begin();
  timeClient.update();
}

void loop() {
  timeClient.update();
  Serial.println(timeClient.getFormattedTime());
}
```

Line-by-Line Explanation:

The device requests current time via UDP from pool servers. offsetSeconds allows for your local time zone. Syncing prevents drift over long uptime periods.

### Homework
Log data to an SD card with a precise date and time stamp for every entry.

## Lesson 59: Multi-Device Communication with MQTT

Key Concepts:

Designing a local home automation network.
Implementing a Broker (e.g., Mosquitto) to act as a message hub.
Using topics (e.g., house/livingroom/temp) for structured messaging.
Establishing "Last Will and Testament" for connectivity monitoring.

```cpp
client.publish("house/livingroom/status", "online", true); // Retained message
client.subscribe("house/kitchen/commands");
```

Line-by-Line Explanation:

MQTT is event-driven; Arduino only acts when data changes. "Retained messages" ensure new subscribers get the last known state immediately upon connecting.

### Homework
Connect two Arduinos: one publishes motion data, the other subscribes to it and triggers an alarm.

## Lesson 60: System Integration & Reliable IoT Architecture

Key Concepts:

Designing "fail-safe" code (auto-reconnect, watchdog timers).
Modular code architecture (splitting files, .h headers).
Remote firmware updates (OTA basics).
Best practices for long-term deployment in the field.

```cpp
// Logic: If status != WL_CONNECTED, call reconnectFunction()
void reconnect() {
  while (WiFi.status() != WL_CONNECTED) {
    WiFi.begin(ssid, pass);
    delay(5000);
  }
}
```

Line-by-Line Explanation:

Focuses on robustness. Watchdog timers reset the Arduino if the code hangs. Modular design makes complex projects (500+ lines) maintainable and debuggable.

### Homework
Combine lessons 56–59 into a single, modular "Smart Hub" project that survives WiFi reboots and logs all events with accurate timestamps.

## Lesson 61: System Integration & Reliable IoT Architecture (Continued)

Key Concepts:

Building fail-safe systems with auto-reconnect logic and watchdog timers.
Modular code using header files (.h) for complex projects.
Basic Over-The-Air (OTA) updates for remote firmware.
Best practices for long-term, field-deployed IoT devices.

```cpp
#include <WiFiS3.h>
void reconnect() {
  while (WiFi.status() != WL_CONNECTED) {
    WiFi.begin(ssid, pass);
    delay(5000);
  }
  Serial.println("Reconnected to WiFi");
}
void loop() {
  if (WiFi.status() != WL_CONNECTED) reconnect();
  // Main project logic here
}
```

Explanation:

The reconnect() function ensures the device recovers from network drops. Watchdog timers (via AVR or R4-specific libraries) reset the board on hangs. Modular design keeps code maintainable beyond 500 lines.

### Homework
Add watchdog and reconnect to your Smart Hub from Lesson 59; test by unplugging the router.

## Lesson 62: First Steps with WiFi on the Arduino Uno R4

Key Concepts:

Enabling the R4's onboard WiFi module.
Scanning for networks and connecting securely.
Basic status checks and IP address retrieval.
Handling connection timeouts gracefully.

```cpp
#include <WiFiS3.h>
char ssid[] = "YourNetwork"; char pass[] = "YourPassword";
void setup() {
  Serial.begin(9600);
  while (WiFi.status() != WL_CONNECTED) {
    WiFi.begin(ssid, pass);
    delay(5000);
  }
  Serial.println("Connected! IP: " + WiFi.localIP().toString());
}
```

Explanation:

WiFiS3.h is specific to the R4's Renesas chip. The loop checks WL_CONNECTED status; localIP() confirms successful connection. Add retries for unreliable networks.

### Homework

Scan and print all available networks with signal strength using WiFi.scanNetworks().

## Lesson 63: First Look at WiFi on the Arduino Uno R4 (Expanded)

Key Concepts:

Advanced WiFi diagnostics (RSSI, encryption type).
Setting up as both client and access point (AP mode).
Energy-efficient connection management.
Integrating with prior sensors (e.g., DHT from earlier lessons).

```cpp
#include <WiFiS3.h>
void setup() {
  Serial.begin(9600);
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) delay(100);
  Serial.print("Signal Strength: "); Serial.println(WiFi.RSSI());
}
```

Explanation:

WiFi.RSSI() gives signal quality in dBm. AP mode (WiFi.beginAP()) turns the R4 into a hotspot. Combine with sensor reads for a basic wireless transmitter.

### Homework

Create a sketch that reports temperature/humidity over Serial only when WiFi is connected and signal > -70 dBm.

## Lesson 64: Installing and Using Thonny for Python Development

Key Concepts:

Setting up Thonny IDE (recommended for beginners).
Running simple Python scripts on your PC to interact with Arduino.
Variable types, basic I/O, and differences from Arduino C++.
Preparing for WiFi data exchange in upcoming lessons.

```python
# Simple Python example (run in Thonny)
x = 7
y = 2.5
z = x + y
print(f"Result: {z}")
```

Explanation:

Thonny provides a clean interface with built-in debugger. Python is interpreted (vs. Arduino's compiled C), making it slower but easier for rapid desktop development. No variable declaration needed, but track types carefully.

### Homework

Write a Python script in Thonny that asks for two numbers, adds them, and prints the result with formatting.

## Lesson 65: Learn Python Essentials in One Session

Key Concepts:

Core Python syntax: loops, conditionals, lists (arrays), and functions.
Differences from Arduino (e.g., indentation instead of braces, dynamic typing).
Input handling, string formatting, and basic math.
Building toward desktop programs that communicate with the R4 over WiFi.

```python
# Example from the lesson (full session covers for/while, if, lists)
grades = []
num = int(input("How many grades? "))
for i in range(num):
    grade = float(input("Enter grade: "))
    grades.append(grade)
print("Average:", sum(grades)/len(grades))
```

Explanation:

Python uses colons (:) and indentation for blocks. Lists replace arrays and support mixed types. range(num) loops from 0 to num-1. This session bridges Arduino knowledge to Python for GUI and data plotting.

### Homework
Modify the grades script to also print the highest and lowest grade using `max()` and `min()`.

## Lesson 66: Passing Data Between Arduino and Desktop Over WiFi

Key Concepts:

Establishing basic TCP client-server communication.
Sending sensor data from Arduino to a Python desktop app.
Receiving commands back (e.g., toggle LED).
Error handling for dropped connections.

```cpp
// Arduino side (server)
#include <WiFiS3.h>
WiFiServer server(80);
void loop() {
  WiFiClient client = server.available();
  if (client) {
    client.println("Temperature: 23.5");
    client.stop();
  }
}
```

```python
# Python side (client - run in Thonny)
import socket
s = socket.socket()
s.connect(('Arduino_IP', 80))
data = s.recv(1024)
print(data.decode())
s.close()
```

Explanation:

Arduino runs a simple server on port 80. Python uses socket to connect and receive data. Expand this for bidirectional control in Lesson 66+.

### Homework

Send DHT22 temperature every 10 seconds from Arduino; display it live in a Python loop.


## Lesson 67: Passing Data Between Arduino and Desktop Over WiFi

Key Concepts:

Setting up the Arduino R4 as a WiFi server.
Using Python sockets on the desktop (via Thonny) as a client.
Sending simple sensor data (e.g., analog readings) from Arduino to PC.
Basic bidirectional data exchange with error handling.

```cpp
// Arduino side (server)
#include <WiFiS3.h>
WiFiServer server(80);
void setup() {
  Serial.begin(9600);
  WiFi.begin(ssid, pass);
  server.begin();
}
void loop() {
  WiFiClient client = server.available();
  if (client) {
    int sensor = analogRead(A0);
    client.println(sensor);
    client.stop();
  }
}
```

```python
# Python side (client - run in Thonny)
import socket
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect(('YOUR_ARDUINO_IP', 80))
data = s.recv(1024).decode().strip()
print("Received:", data)
s.close()
```

Explanation:

The Arduino listens on port 80 and sends an analog sensor value on connection. Python connects via TCP socket, receives the data, and prints it. This forms the foundation for remote monitoring.

### Homework
Modify to send temperature from a DHT sensor and display it in Python every 5 seconds.

## Lesson 68: Control LED on Arduino Using Client Server Connection

Key Concepts:

Expanding the client-server model for command sending.
Arduino parsing incoming strings (e.g., "ON" or "OFF").
Desktop Python sending toggle commands over WiFi.
Adding feedback (e.g., current LED state returned to client).

```cpp
// Arduino side
#include <WiFiS3.h>
WiFiServer server(80);
const int ledPin = 13;
void setup() { /* WiFi & server init as before */ }
void loop() {
  WiFiClient client = server.available();
  if (client) {
    String cmd = client.readStringUntil('\n');
    if (cmd.indexOf("ON") != -1) digitalWrite(ledPin, HIGH);
    if (cmd.indexOf("OFF") != -1) digitalWrite(ledPin, LOW);
    client.println("LED state updated");
    client.stop();
  }
}
```

```python
# Python side
import socket
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect(('YOUR_ARDUINO_IP', 80))
s.sendall(b"ON\n")
response = s.recv(1024).decode().strip()
print(response)
s.close()
```

Explanation:

Arduino reads incoming commands as strings and controls the LED accordingly. Python sends the command byte string. This enables remote control without a physical GUI yet.

### Homework: Add support for brightness control (using PWM on pin 9) with values like "BRIGHT:128".

## Lesson 69: Introduction to PyQt5 for Graphical Interfaces

Key Concepts:

Installing and using PyQt5 in Thonny for desktop GUIs.
Creating a basic window with buttons that trigger WiFi commands.
Linking GUI events (button clicks) to socket sends from Lesson 67.
Laying groundwork for live data display and control dashboards.

```python
# Basic PyQt5 GUI example (Python side)
import sys
from PyQt5.QtWidgets import QApplication, QWidget, QPushButton, QVBoxLayout
import socket

class ArduinoGUI(QWidget):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Arduino WiFi Control")
        layout = QVBoxLayout()
        on_btn = QPushButton("LED ON")
        on_btn.clicked.connect(self.send_on)
        layout.addWidget(on_btn)
        self.setLayout(layout)
        self.show()

    def send_on(self):
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.connect(('YOUR_ARDUINO_IP', 80))
            s.sendall(b"ON\n")
            s.close()
            print("Command sent: ON")
        except:
            print("Connection error")

if __name__ == '__main__':
    app = QApplication(sys.argv)
    gui = ArduinoGUI()
    sys.exit(app.exec_())
```

Explanation:

This creates a simple window with an "LED ON" button. Clicking it establishes a socket connection and sends the command to the Arduino. Extend with more buttons and status labels in later lessons.

### Homework
Add an "LED OFF" button and a label that updates with Arduino feedback (e.g., current state).
