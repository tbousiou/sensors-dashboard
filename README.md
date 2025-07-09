# Sensors Dashboard

A real-time sensor monitoring dashboard built with PHP, MySQL, and Tailwind CSS. Track sensor readings, view analytics, and manage sensor data through a clean web interface.

## Features

- **Real-time Dashboard**: Monitor sensor readings with auto-refresh functionality
- **Sensor Management**: Track multiple sensors with status indicators
- **Analytics**: View cumulative data trends and insights
- **API Integration**: RESTful API for posting sensor data
- **Authentication**: Secure login system with session management
- **Responsive Design**: Works on desktop and mobile devices

## Screenshots

- Dashboard with sensor cards showing real-time data
- Analytics page with sensor selection and data visualization
- Clean, modern interface with Tailwind CSS

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx) or PHP built-in server

## Installation

### 1. Clone the repository
```bash
git clone https://github.com/yourusername/sensors-dashboard.git
cd sensors-dashboard
```

### 2. Install dependencies
```bash
composer install
```

### 3. Database Setup

Create a MySQL database and import the schema:

```sql
CREATE DATABASE sensors_dashboard;

CREATE TABLE sensors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    volume_per_hit DECIMAL(10,2) DEFAULT 0.50,
    unit VARCHAR(10) DEFAULT 'L',
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensors(id) ON DELETE CASCADE,
    INDEX idx_sensor_timestamp (sensor_id, timestamp)
);
```

### 4. Environment Configuration

Copy the environment file and configure your settings:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=sensors_dashboard
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Authentication
AUTH_USERNAME=admin
AUTH_PASSWORD=your_secure_password

# API Configuration
API_KEY=your_secure_api_key_here
```

### 5. Web Server Setup

#### Using PHP Built-in Server (Development)
```bash
# Start from project root
php -S localhost:8000 -t public
```

#### Using Apache/Nginx (Production)
Point your web server document root to the `public/` directory.

## Usage

### Web Interface

1. **Login**: Visit `http://localhost:8000` and login with your credentials
2. **Dashboard**: View all sensors with real-time data
3. **Analytics**: Click on sensor names or "View Analytics" for detailed insights
4. **Auto-refresh**: Dashboard automatically refreshes every 30 seconds

### API Endpoints

#### POST Sensor Data
```bash
curl -X POST \
  -H "X-API-Key: your_api_key" \
  "http://localhost:8000/api/post_sensor_data.php?sensor_id=1"
```

**Parameters:**
- `sensor_id` (required): ID of the sensor
- `timestamp` (optional): Custom timestamp in format `YYYY-MM-DD HH:MM:SS`

**Response:**
```json
{
    "success": true,
    "hit_id": 123,
    "sensor_id": "1",
    "sensor_name": "Sensor 1",
    "timestamp": "2024-07-09 14:30:00",
    "message": "Sensor data recorded successfully"
}
```

#### Authentication Methods
API key can be provided via:
- Header: `X-API-Key: your_key`
- POST data: `api_key=your_key`
- Query parameter: `?api_key=your_key`

## Project Structure

```
sensors-dashboard/
├── public/                  # Web accessible files
│   ├── index.php           # Main dashboard
│   ├── login.php           # Authentication
│   ├── analytics.php       # Analytics page
│   ├── api/               # API endpoints
│   │   └── post_sensor_data.php
│   └── assets/            # Static assets
├── app/                    # Application logic
│   ├── config/            # Configuration files
│   └── includes/          # PHP includes
├── .env                   # Environment variables
├── composer.json          # Dependencies
└── README.md             # This file
```

## Configuration

### Database Schema

**Sensors Table:**
- `id`: Primary key
- `name`: Sensor name
- `location`: Physical location
- `volume_per_hit`: Volume measurement per hit
- `unit`: Measurement unit (L, mL, etc.)
- `status`: active, inactive, maintenance

**Readings Table:**
- `id`: Primary key
- `sensor_id`: Foreign key to sensors
- `timestamp`: Reading timestamp

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | Database host | localhost |
| `DB_NAME` | Database name | sensors_dashboard |
| `DB_USERNAME` | Database username | - |
| `DB_PASSWORD` | Database password | - |
| `AUTH_USERNAME` | Admin username | admin |
| `AUTH_PASSWORD` | Admin password | - |
| `API_KEY` | API authentication key | - |

## Development

### Adding New Sensors

Insert sensors via SQL:
```sql
INSERT INTO sensors (name, location, volume_per_hit, unit, status) 
VALUES ('Sensor 1', 'Building A', 0.50, 'L', 'active');
```

### Customizing the UI

The dashboard uses Tailwind CSS for styling. Main components:
- **Dashboard**: `public/index.php`
- **Sensor Cards**: Responsive grid layout
- **Analytics**: `public/analytics.php`
- **Authentication**: `public/login.php`

### API Integration

Example Python script to post sensor data:
```python
import requests
import json

url = "http://localhost:8000/api/post_sensor_data.php"
headers = {"X-API-Key": "your_api_key"}
params = {"sensor_id": 1}

response = requests.post(url, headers=headers, params=params)
print(response.json())
```

## Security

- **Authentication**: Session-based login system
- **API Security**: API key authentication for endpoints
- **Input Validation**: Prepared statements prevent SQL injection
- **Environment Variables**: Sensitive data stored in `.env` file
- **File Structure**: Private files outside web root

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `.env`
   - Ensure MySQL service is running
   - Verify database exists

2. **API Key Invalid**
   - Check API key in `.env` file
   - Verify key format in request

3. **File Not Found Errors**
   - Ensure server is started from correct directory
   - Check file paths in includes

4. **Permission Denied**
   - Check file permissions
   - Ensure web server has read access

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues and questions:
- Create an issue on GitHub
- Check the troubleshooting section above
- Review the configuration settings

## Changelog

### Version 1.0.0
- Initial release with basic dashboard
- Sensor management and monitoring
- API endpoint for data collection
- Authentication system
- Responsive design