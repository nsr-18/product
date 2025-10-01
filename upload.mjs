const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const app = express();
const port = 3000;

// Create the uploads directory if it doesn't exist
const uploadDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir);
}

// Configure multer for file storage
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, uploadDir);
    },
    filename: (req, file, cb) => {
        // Use the original file name with a unique prefix to avoid conflicts
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + '-' + file.originalname);
    }
});
const upload = multer({ storage: storage });

// Serve the HTML file
app.use(express.static(path.join(__dirname, '')));

// Handle the POST request from the form
app.post('/upload', upload.array('files'), (req, res) => {
    try {
        const { rawText, videoLink } = req.body;
        const uploadedFiles = req.files;

        // Log the received data
        console.log('Text content:', rawText);
        console.log('Video link:', videoLink);
        console.log('Uploaded files:', uploadedFiles.map(f => f.filename));

        // In a real application, you would save this data to a database.
        // For this example, we'll just send a success response.
        res.status(200).json({
            message: 'Content uploaded successfully!',
            files: uploadedFiles.map(file => ({
                filename: file.filename,
                size: file.size
            }))
        });
    } catch (error) {
        console.error('Upload error:', error);
        res.status(500).json({ message: 'An error occurred during upload.' });
    }
});

app.listen(port, () => {
    console.log(`Server is running at http://localhost:${port}`);
});