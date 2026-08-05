-- 1. Create Database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS `stuff` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `stuff`;

-- 2. Create Locations Table
CREATE TABLE IF NOT EXISTS `Locations` (
  `LocationID` INT AUTO_INCREMENT PRIMARY KEY,
  `Location` VARCHAR(100) NOT NULL,
  `LocationDescription` TEXT DEFAULT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Material Types Table
CREATE TABLE IF NOT EXISTS `MaterialTypes` (
  `MatTypeID` INT AUTO_INCREMENT PRIMARY KEY,
  `MaterialType` VARCHAR(100) NOT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create Materials Table
CREATE TABLE IF NOT EXISTS `Materials` (
  `MaterialID` INT AUTO_INCREMENT PRIMARY KEY,
  `Name` VARCHAR(255) NOT NULL,
  `MaterialDescription` TEXT DEFAULT NULL,
  `Quantity` INT NOT NULL DEFAULT 1,
  `Photo` VARCHAR(255) DEFAULT NULL,
  `MatTypeID` INT DEFAULT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`MatTypeID`) REFERENCES `MaterialTypes`(`MatTypeID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create WhereThingsAreStored Junction Table (Location Mapping)
CREATE TABLE IF NOT EXISTS `WhereThingsAreStored` (
  `MappingID` INT AUTO_INCREMENT PRIMARY KEY,
  `MaterialID` INT NOT NULL,
  `LocationID` INT NOT NULL,
  `AssignedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`MaterialID`) REFERENCES `Materials`(`MaterialID`) ON DELETE CASCADE,
  FOREIGN KEY (`LocationID`) REFERENCES `Locations`(`LocationID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Sample Seed Data (Optional - useful for testing)
-- -----------------------------------------------------------------------------

-- Insert Sample Locations
INSERT INTO `Locations` (`LocationID`, `Location`, `LocationDescription`) VALUES
(1, 'Box 1', 'Main Workbench Bay'),
(2, 'Box 2', 'Gousto Box 2 - White Shelves (Top Row)'),
(6, 'Box 6', 'Under Main Counter'),
(9, 'Box 9', 'Heavy Duty Rack - Shelf 3'),
(11, 'Box 11', 'Paint Cabinet');

-- Insert Sample Material Types
INSERT INTO `MaterialTypes` (`MatTypeID`, `MaterialType`) VALUES
(1, 'Consumable'),
(2, 'Tool'),
(3, 'Hardware / Fasteners'),
(4, 'Chemicals / Fluids');
