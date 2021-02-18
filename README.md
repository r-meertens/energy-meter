# energy-meter
Reads energy meters and shows usage statistics via webapp

## installation ##
* Install InfluxDB. (Tested with v1.15)
* Install the YTLmodbus.py script from Psy0rz (https://github.com/psy0rz/YTLmodbus) for reading the DDS353H-3 meters and filling InfluxDB.
* Install Apache webserver with PHP enabled.
* Copy the webapp folder to the Apache documentroot.