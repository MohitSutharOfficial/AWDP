// Serverless function for contact form
import mysql from 'mysql2/promise';

export default async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    res.status(200).end();
    return;
  }

  if (req.method !== 'POST') {
    return res.status(405).json({ 
      success: false, 
      message: 'Method not allowed' 
    });
  }

  try {
    // Database connection
    const connection = await mysql.createConnection({
      host: process.env.DB_HOST,
      user: process.env.DB_USER,
      password: process.env.DB_PASSWORD,
      database: process.env.DB_NAME,
      ssl: {
        rejectUnauthorized: false
      }
    });

    // Validate and sanitize input
    const { name, email, phone, company, subject, message } = req.body;
    
    // Basic validation
    if (!name || !email || !message) {
      return res.status(400).json({
        success: false,
        message: 'Name, email, and message are required'
      });
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return res.status(400).json({
        success: false,
        message: 'Please provide a valid email address'
      });
    }

    // Insert contact into database
    const [result] = await connection.execute(
      'INSERT INTO contacts (name, email, phone, company, subject, message, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
      [
        name.trim(),
        email.trim().toLowerCase(),
        phone?.trim() || null,
        company?.trim() || null,
        subject?.trim() || null,
        message.trim()
      ]
    );

    await connection.end();

    // Send success response
    res.status(200).json({
      success: true,
      message: 'Thank you! Your message has been sent successfully.',
      id: result.insertId
    });

  } catch (error) {
    console.error('Database error:', error);
    
    res.status(500).json({
      success: false,
      message: 'Sorry, there was an error sending your message. Please try again.'
    });
  }
}
