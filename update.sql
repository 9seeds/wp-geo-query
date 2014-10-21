-- Create Tables
DROP TABLE IF EXISTS wp_geolocations;

CREATE TABLE wp_geolocations (
    location_id INT UNSIGNED PRIMARY KEY,
    country VARCHAR(255),
    region VARCHAR(255),
    city VARCHAR(255),
    postal_code VARCHAR(10),
    latitude DECIMAL(7,4),
    longitude DECIMAL(7,4),
    metro_code VARCHAR(255),
    area_code VARCHAR(255)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS wp_geoblocks;

CREATE TABLE wp_geoblocks (
    start_ip_num INT UNSIGNED,
    end_ip_num INT UNSIGNED,
    location_id INT,
	idx INT
) DEFAULT CHARSET=utf8;


-- Do import (files must be in /var/lib/mysql/db_name/)
LOAD DATA INFILE 'GeoLiteCity-Location.csv'
INTO TABLE wp_geolocations
FIELDS TERMINATED BY ','
OPTIONALLY ENCLOSED BY '"'
IGNORE 2 LINES;

LOAD DATA INFILE 'GeoLiteCity-Blocks.csv'
INTO TABLE wp_geoblocks
FIELDS TERMINATED BY ','
OPTIONALLY ENCLOSED BY '"'
IGNORE 2 LINES;

-- re-add indexes
UPDATE wp_geoblocks SET idx = (end_ip_num - (end_ip_num % 65536));
CREATE INDEX geoidx ON wp_geoblocks(idx);
CREATE INDEX geozip on wp_geolocations(postal_code);
