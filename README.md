# File Server

The `file-server` is a robust, scalable PHP-based file-serving backend designed to securely store and serve files for other backend systems. It supports client authentication (for backend services), file uploads, and secure file access via one-time-use links. The server uses a custom JWT implementation for authentication, MySQL for metadata storage, and a local filesystem for file storage. It is Dockerized for easy deployment and scalability.

This server does not interact directly with end users. Instead, it serves as a backend service for other backend systems (e.g., a content management or application backend) that handle user authentication and permissions. The client backend authenticates with the file server, uploads files, and requests one-time-use links, which it then delivers to end users (e.g., via a client application).

## Table of Contents
- [File Server](#file-server)
  - [Table of Contents](#table-of-contents)
  - [Features](#features)
  - [Architecture](#architecture)
  - [Prerequisites](#prerequisites)
  - [Project Structure](#project-structure)
  - [Setup Instructions](#setup-instructions)
    - [1. Clone the Repository](#1-clone-the-repository)
    - [2. Configure Environment Variables](#2-configure-environment-variables)
    - [3. Build and Run with Docker Compose](#3-build-and-run-with-docker-compose)
    - [4. Stop the Services](#4-stop-the-services)
    - [5. Test the API](#5-test-the-api)
  - [API Endpoints](#api-endpoints)
    - [1. Signup (`/api/signup`)](#1-signup-apisignup)
    - [2. Login (`/api/login`)](#2-login-apilogin)
    - [3. Upload File (`/api/upload_file`)](#3-upload-file-apiupload_file)
    - [4. Request File (`/api/request_file`)](#4-request-file-apirequest_file)
    - [5. Serve File (`/api/serve_file`)](#5-serve-file-apiserve_file)
  - [Docker Configuration](#docker-configuration)
    - [Dockerfile](#dockerfile)
    - [Docker Compose](#docker-compose)
    - [Build and Run](#build-and-run)
    - [Stop](#stop)
  - [Security Considerations](#security-considerations)
  - [Scalability](#scalability)
  - [Integration](#integration)
  - [Troubleshooting](#troubleshooting)
  - [Usage Example](#usage-example)
  - [Contributing](#contributing)
  - [License](#license)

## Features
- **Backend Authentication**: Custom JWT-based authentication for backend clients.
- **Client Signup**: Allows backend systems to register with the server.
- **File Upload**: Supports uploading files (e.g., PDFs, videos) with validation for type and size.
- **One-Time-Use Links**: Generates secure, expiring links for file access.
- **File Serving**: Streams files from a local folder with proper MIME types.
- **MySQL Storage**: Stores file metadata, client credentials, and link tokens.
- **Dockerized**: Easy deployment with Docker and Docker Compose.
- **Security**: Input validation, restricted file access, and HTTPS-ready setup.
- **Scalability**: Modular code, database indexing, and support for load balancing.

## Architecture
The file server consists of:
- **PHP Application**: A plain PHP application (no frameworks) handling API requests for client signup, login, file upload, link generation, and file serving.
- **MySQL Database**: Stores API client credentials, file metadata, and one-time link tokens.
- **File Storage**: Files stored in a local `/uploads` folder, organized in subdirectories.
- **Nginx**: Serves the PHP application via FastCGI.
- **Docker**: Containers for PHP/Nginx and MySQL, orchestrated with Docker Compose.

**Workflow**:
1. The client backend signs up with the file server (`/api/signup`).
2. The client backend authenticates (`/api/login`) to obtain a JWT.
3. The client backend uploads files (`/api/upload_file`), receiving a `file_id`.
4. The client backend requests a one-time-use link (`/api/request_file`) for a file.
5. The file server returns a link, which the client backend delivers to the end user.
6. The end user accesses the file via the link (`/api/serve_file`).

## Prerequisites
- **Docker**: [Install Docker](https://docs.docker.com/get-docker/)
- **Docker Compose**: [Install Docker Compose](https://docs.docker.com/compose/install/)
- **PHP Knowledge**: Basic understanding of PHP for customization (optional).
- **MySQL Knowledge**: For database management (optional).
- **Git**: To clone the repository.
- **Java**: For running the usage example (JDK 11+ recommended).

## Project Structure
```
file-server/
├── uploads/                 # File storage (Docker volume)
├── logs/                    # Log files (Docker volume)
├── src/
│   ├── config/
│   │   ├── database.php     # Database connection
│   │   ├── env.php          # Environment variable loader
│   ├── lib/
│   │   ├── jwt.php          # Custom JWT implementation
│   │   ├── db.php           # Database queries
│   │   ├── file.php         # File handling (upload/serve)
│   │   ├── utils.php        # Utility functions
│   ├── api/
│   │   ├── signup.php       # Client signup endpoint
│   │   ├── login.php        # Client login endpoint
│   │   ├── upload_file.php  # File upload endpoint
│   │   ├── request_file.php # Generate one-time-use link
│   │   ├── serve_file.php   # Serve file via token
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf       # Nginx configuration
│   ├── php/
│   │   ├── php.ini          # PHP configuration
│   ├── mysql/
│   │   ├── init.sql         # MySQL schema initialization
├── Dockerfile               # PHP/Nginx Docker image
├── docker-compose.yml       # Docker Compose configuration
├── .env                     # Environment variables
├── index.php                # API routing
```

## Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/ktauchathuranga/file-server.git
cd file-server
```

### 2. Configure Environment Variables
Create a `.env` file in the root directory (or use the environment variables in `docker-compose.yml`):

```env
DB_HOST=db
DB_PORT=3306
DB_NAME=file_serving_db
DB_USER=root
DB_PASS=root
JWT_SECRET=your_random_jwt_secret_32_chars_minimum
UPLOAD_DIR=/var/www/html/uploads
```

- **JWT_SECRET**: Generate a random 32+ character string (e.g., using `openssl rand -base64 32`).
- **DB_PASS**: Ensure it’s secure for production.

### 3. Build and Run with Docker Compose
```bash
COMPOSE_BAKE=true docker-compose up --build
```

This will:
- Build the PHP/Nginx Docker image.
- Start the PHP service on `http://localhost:8080`.
- Start the MySQL service and initialize the database.
- Mount `/uploads` and `/logs` as volumes.

To run in the background:
```bash
COMPOSE_BAKE=true docker-compose up --build -d
```

### 4. Stop the Services
```bash
docker-compose down
```

To reset volumes:
```bash
docker-compose down -v
```

### 5. Test the API
Use `curl` or the Java client in the [Usage Example](#usage-example) to test the endpoints.

**Sign Up**:
```bash
curl -X POST http://localhost:8080/api/signup \
-H "Content-Type: application/json" \
-d '{"client_name":"test_backend","client_secret":"your_client_secret"}'
```

**Log In**:
```bash
curl -X POST http://localhost:8080/api/login \
-H "Content-Type: application/json" \
-d '{"client_name":"test_backend","client_secret":"your_client_secret"}'
```

**Upload File**:
```bash
curl -X POST http://localhost:8080/api/upload_file \
-H "Authorization: Bearer your_jwt_token" \
-F "file=@/path/to/document.pdf"
```

**Request Link**:
```bash
curl -X POST http://localhost:8080/api/request_file \
-H "Authorization: Bearer your_jwt_token" \
-H "Content-Type: application/json" \
-d '{"file_id":"1"}'
```

**Download File**:
```bash
curl http://localhost:8080/api/serve_file?token=some_random_token --output downloaded_file.pdf
```

## API Endpoints

### 1. Signup (`/api/signup`)
- **Method**: POST
- **Content-Type**: application/json
- **Body**:
  ```json
  {
    "client_name": "string",
    "client_secret": "string"
  }
  ```
- **Response**:
  - `201 Created`: `{ "message": "Client created", "client_id": 1 }`
  - `400 Bad Request`: Missing or invalid fields (client_name < 3 chars, client_secret < 8 chars).
  - `409 Conflict`: Client name already exists.

### 2. Login (`/api/login`)
- **Method**: POST
- **Content-Type**: application/json
- **Body**:
  ```json
  {
    "client_name": "string",
    "client_secret": "string"
  }
  ```
- **Response**:
  - `200 OK`: `{ "token": "jwt_token" }`
  - `400 Bad Request`: Missing fields.
  - `401 Unauthorized`: Invalid credentials.

### 3. Upload File (`/api/upload_file`)
- **Method**: POST
- **Content-Type**: multipart/form-data
- **Headers**: `Authorization: Bearer jwt_token`
- **Form Data**: `file` (file to upload)
- **Response**:
  - `201 Created`: `{ "message": "File uploaded", "file_id": 1 }`
  - `400 Bad Request`: No file or invalid file type/size.
  - `401 Unauthorized`: Invalid JWT.

**Supported File Types**: PDF, MP4, JPEG, PNG (configurable in `src/lib/file.php`).
**Max File Size**: 100MB (configurable in `docker/php/php.ini`).

### 4. Request File (`/api/request_file`)
- **Method**: POST
- **Content-Type**: application/json
- **Headers**: `Authorization: Bearer jwt_token`
- **Body**:
  ```json
  {
    "file_id": "string"
  }
  ```
- **Response**:
  - `200 OK`: `{ "url": "http://localhost:8080/api/serve_file.php?token=some_token" }`
  - `400 Bad Request`: Missing file_id.
  - `401 Unauthorized`: Invalid JWT.
  - `404 Not Found`: File not found.

### 5. Serve File (`/api/serve_file`)
- **Method**: GET
- **Query Parameter**: `token=some_random_token`
- **Response**:
  - `200 OK`: File streamed with correct MIME type and filename.
  - `400 Bad Request`: Missing token.
  - `403 Forbidden`: Invalid or expired token.

**Link Expiration**: 30 minutes (configurable in `src/lib/db.php`).

## Docker Configuration

### Dockerfile
- **Base Image**: `php:8.1-fpm`
- **Dependencies**: Nginx, `libmagic-dev`, PHP extensions (`pdo_mysql`, `fileinfo`).
- **Setup**: Copies project files, configures PHP/Nginx, sets permissions for `/uploads` and `/logs`.
- **Port**: Exposes 80 (mapped to 8080 on the host).

### Docker Compose
- **Services**:
  - **php**: Runs PHP-FPM and Nginx, accessible at `http://localhost:8080`.
  - **db**: MySQL 8.0, initialized with `init.sql`.
- **Volumes**:
  - `db_data`: Persists MySQL data.
  - `uploads`: Persists uploaded files.
  - `logs`: Persists application logs.
- **Networks**: Uses a bridge network (`file-serving-network`) for service communication.

### Build and Run
```bash
COMPOSE_BAKE=true docker-compose up --build
```

### Stop
```bash
docker-compose down
```

## Security Considerations
- **JWT**:
  - Uses HMAC-SHA256 with a strong `JWT_SECRET`.
  - Tokens expire after 1 hour.
  - Validates signature and expiration on every request.
- **File Upload**:
  - Restricts file types (PDF, MP4, JPEG, PNG).
  - Limits file size to 100MB.
  - Uses unique file names to prevent overwrites.
  - Validates MIME types with `fileinfo`.
- **File Access**:
  - One-time-use links expire after 30 minutes.
  - `/uploads` folder is inaccessible via HTTP (Nginx `deny all`).
- **Database**:
  - Uses prepared statements to prevent SQL injection.
  - Client secrets hashed with BCrypt.
- **Docker**:
  - Isolated containers with restricted permissions.
  - MySQL root password set via environment.
- **Production**:
  - Enable HTTPS (e.g., via reverse proxy).
  - Store secrets in a secure vault.
  - Regularly back up `uploads` and `db_data` volumes.

## Scalability
- **Database**:
  - Indexes on `client_name`, `file_id`, and `token` for fast queries.
  - Supports connection pooling for high traffic.
- **File Storage**:
  - Files organized in `/uploads/files/` to avoid filesystem bottlenecks.
  - Can be extended to use a distributed filesystem (e.g., NFS).
- **Docker**:
  - Scale PHP service: `docker-compose up --scale php=3`.
  - Use a load balancer (e.g., Nginx, Traefik) in production.
- **Caching**:
  - Add file-based caching for frequent metadata queries (optional).

## Integration
The file server is designed to integrate with any backend system that needs secure file storage and delivery:
- **Client Backend**:
  - Signs up via `/api/signup` (one-time).
  - Authenticates via `/api/login` to get a JWT.
  - Uploads files via `/api/upload_file`, storing `file_id` in its database.
  - Requests one-time-use links via `/api/request_file` for authorized users.
  - Delivers links to users via a client application (e.g., web or desktop app).
- **Database**:
  - Store `file_id` in the client backend’s database to associate files with resources.
- **Client Application**:
  - Use the provided Java client code (see [Usage Example](#usage-example)) to interact with the file server.

## Troubleshooting
- **Build Fails**:
  - Check `docker logs file_serving_php` for errors.
  - Ensure `libmagic-dev` is installed (corrected in `Dockerfile`).
- **MySQL Connection**:
  - Verify `DB_HOST=db` in environment variables.
  - Check `docker logs file_serving_db` for MySQL errors.
- **File Upload Issues**:
  - Ensure the uploaded file is within size (100MB) and type limits.
  - Check `/uploads/files/` permissions in the container.
- **Port Conflict**:
  - If `8080` is in use, edit `docker-compose.yml` (e.g., `8081:80`).
- **Logs**:
  - Application logs: `./logs/app.log`.
  - Nginx logs: `docker logs file_serving_php`.

## Usage Example
Below is a complete Java example demonstrating how a backend system can interact with the file server. The example signs up a client, logs in, uploads a file, requests a one-time-use link, and downloads the file to verify the workflow.

```java
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.file.Files;
import java.nio.file.Path;

public class FileServerClient {
    private static final String API_URL = "http://localhost:8080/api";
    private static final HttpClient client = HttpClient.newHttpClient();

    public static String signup(String clientName, String clientSecret) throws Exception {
        String json = String.format("{\"client_name\": \"%s\", \"client_secret\": \"%s\"}", clientName, clientSecret);
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(API_URL + "/signup"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(json))
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 201) {
            return response.body().split("\"client_id\":")[1].split("}")[0];
        }
        throw new Exception("Signup failed: " + response.body());
    }

    public static String login(String clientName, String clientSecret) throws Exception {
        String json = String.format("{\"client_name\": \"%s\", \"client_secret\": \"%s\"}", clientName, clientSecret);
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(API_URL + "/login"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(json))
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 200) {
            return response.body().split("\"token\":\"")[1].split("\"")[0];
        }
        throw new Exception("Login failed: " + response.body());
    }

    public static String uploadFile(String token, File file) throws Exception {
        String boundary = "----" + System.currentTimeMillis();
        MultiPartBodyPublisher publisher = new MultiPartBodyPublisher(boundary)
                .addPart("file", file.toPath(), "file");
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(API_URL + "/upload_file"))
                .header("Authorization", "Bearer " + token)
                .header("Content-Type", "multipart/form-data; boundary=" + boundary)
                .POST(publisher.build())
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 201) {
            return response.body().split("\"file_id\":")[1].split("}")[0];
        }
        throw new Exception("Upload failed: " + response.body());
    }

    public static String requestFile(String token, String fileId) throws Exception {
        String json = String.format("{\"file_id\": \"%s\"}", fileId);
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(API_URL + "/request_file"))
                .header("Authorization", "Bearer " + token)
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(json))
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 200) {
            return response.body().split("\"url\":\"")[1].split("\"")[0];
        }
        throw new Exception("Request failed: " + response.body());
    }

    public static void downloadFile(String url, String outputPath) throws Exception {
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .GET()
                .build();
        HttpResponse<byte[]> response = client.send(request, HttpResponse.BodyHandlers.ofByteArray());
        if (response.statusCode() == 200) {
            try (FileOutputStream fos = new FileOutputStream(outputPath)) {
                fos.write(response.body());
            }
        } else {
            throw new Exception("Download failed: " + response.statusCode());
        }
    }

    public static void main(String[] args) {
        try {
            // Step 1: Sign up a new client (one-time, comment out after first run)
            String clientId = signup("test_backend", "your_client_secret");
            System.out.println("Client ID: " + clientId);

            // Step 2: Log in to get a JWT
            String token = login("test_backend", "your_client_secret");
            System.out.println("JWT: " + token);

            // Step 3: Upload a file
            File file = new File("path/to/document.pdf"); // Replace with actual file path
            String fileId = uploadFile(token, file);
            System.out.println("Uploaded File ID: " + fileId);

            // Step 4: Request a one-time-use link
            String fileUrl = requestFile(token, fileId);
            System.out.println("File URL: " + fileUrl);

            // Step 5: Download the file to verify
            String outputPath = "downloaded_document.pdf";
            downloadFile(fileUrl, outputPath);
            System.out.println("File downloaded to: " + outputPath);

            // The backend can now deliver fileUrl to the end user
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}

class MultiPartBodyPublisher {
    private final String boundary;
    private final ByteArrayOutputStream baos = new ByteArrayOutputStream();

    public MultiPartBodyPublisher(String boundary) {
        this.boundary = boundary;
    }

    public MultiPartBodyPublisher addPart(String name, Path filePath, String fieldName) throws Exception {
        String fileName = filePath.getFileName().toString();
        String mimeType = Files.probeContentType(filePath);
        baos.write(("--" + boundary + "\r\n").getBytes());
        baos.write(("Content-Disposition: form-data; name=\"" + fieldName + "\"; filename=\"" + fileName + "\"\r\n").getBytes());
        baos.write(("Content-Type: " + mimeType + "\r\n\r\n").getBytes());
        baos.write(Files.readAllBytes(filePath));
        baos.write("\r\n".getBytes());
        return this;
    }

    public HttpRequest.BodyPublisher build() {
        baos.write(("--" + boundary + "--\r\n").getBytes());
        return HttpRequest.BodyPublishers.ofByteArray(baos.toByteArray());
    }
}
```

**Instructions**:
1. Save the code as `FileServerClient.java`.
2. Replace `"path/to/document.pdf"` with the path to a real PDF file.
3. Ensure the file server is running (`docker-compose up`).
4. Compile and run:
   ```bash
   javac FileServerClient.java
   java FileServerClient
   ```
5. Comment out the signup call after the first run to avoid duplicate client errors.

## Contributing
Contributions are welcome! To contribute:
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature`).
3. Commit changes (`git commit -m "Add your feature"`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

Please include tests and update documentation as needed.

## License
This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
