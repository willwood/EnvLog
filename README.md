# EnvLog

EnvLog is a lightweight web application that allows users to log environmental data by scanning a location name (using a QR code) or selecting a location from a menu. 

Compatible with all internet-connected devices (smartphones, tablets, laptops and computers). EnvLog runs in the web browser; which negates the need to install any costly or complicated software.

Form fields are added using HTML5 markup, with submitted field names and values stored as key / value pairs in JSON format. Multiple users are able to submit data. EnvLog is coded to confidently handle millions of data submissions. 

## ✨ Features

- Scan a QR code or manually select a location.
- Customisable data input fields (e.g., soil moisture, crop height, air quality and weather observations).
- Data saved with timestamp and location.
- A clean, lightweight and accessible interface.
- Data easily exportable in CSV and JSON format for further analysis.
- View locations on an interactive satellite or street map.

## 🚀 Installation

### Requirements

- A web server running Apache, Nginx, or similar.
- PHP 7.4 or higher.
- MySQL or MariaDB database.

### Download

You can download EnvLog in one of two ways:

- **Clone the repository**:
  ```bash
  git clone https://github.com/willwood/EnvLog.git
  ```
- **Or download the ZIP**:
  - Click on **Code** > **Download ZIP**.

### Setup

1. **Upload EnvLog to a directory or sub domain on your web server**.

2. **Database setup**:
   - Create a new database (e.g., `envlog_db`) using phpMyAdmin or similar.
   - Create a new username and password combination for the database.
   - Make a note of the database credentials.

3. **Configure EnvLog**:
   - Rename the `config.sample.php` file to `config.php`.
   - Using a code editor, edit `config.php` to add your database connection details.
   - Any other settings in `config.php` can be changed as needed.

4. **Access EnvLog**:
   - Visit your domain or IP address where you installed EnvLog to begin using the system.
   - The database tables and schema are automatically generated for you.

## ⚙️ Configuring input fields

EnvLog uses standard HTML5 markup for its form input fields. The completed form is EN-301-549 compliant. In essence, the markup for each form field looks something like this:

```HTML
<div class="envlog_input_item">
  <label for="soil_moisture">Soil Moisture</label>
  <div class="envlog_input_group">
    <input type="number" name="soil_moisture" id="soil_moisture" step="0.01">
    <span class="envlog_input_text">%</span>
  </div>
</div>
```

You can have as many or as few inputs as you like. They are styled similar to [Bootstrap](https://getbootstrap.com) form inputs. You have the option to add prefix or suffix labels to the inputs to denote units of measurement. All common HTML [input types](https://developer.mozilla.org/en-US/docs/Learn_web_development/Extensions/Forms/HTML5_input_types) are supported. 

Just make sure every input you add has a unique `id`, `for` and `name` attribute. The name of the input and its value are what get logged in the database. Data is recorded in the same order that the input fields are presented in.

## 🔗 Dependencies

EnvLog uses two front-end libraries to enhance user interaction: a QR code scanner and an interactive map view. These are open-source, widely supported, and easy to maintain.

1. **html5-qrcode** This JavaScript library handles scanning of QR codes directly via the user's camera. It powers the "Scan QR Code" feature used to pre-select a location on the home screen: [github.com/mebjas/html5-qrcode](https://github.com/mebjas/html5-qrcode)

2. **Leaflet.js** Leaflet is a modern, lightweight library for interactive maps. It is used in EnvLog to visually pin each location using its coordinates: [leafletjs.com](https://leafletjs.com/)

If you're using **NPM (Node Package Manager)** for managing front-end libraries, you can update the dependencies by opening Terminal and navigating into the EnvLog directory with the `cd` command like this: 

```bash
cd EnvLog
```

Then run this command to update the dependencies to their latest stable versions: 

```bash
npm update
```

## 📤 Data viewing and export

All data is stored in the database in JSON format, making it easy to:

- View online or export data sets in CSV and JSON formats.
- Use for offline analysis in software like Excel, SAS, R, or Python.
- Visualise trends over time.

## 🛡 Licence

This project is licensed under the MIT Licence — see the [LICENSE](LICENSE) file for details.

## 🙌 Contributing

Contributions are very welcome. If you spot a bug, have ideas for new features, or would like to assist with translations, please open an issue or submit a pull request.

## 📬 Contact

If you would like to discuss using EnvLog, having it customised for your requirements or you want to suggest improvements, feel free to contact me at [VerdantBytes](https://verdantbytes.com).
