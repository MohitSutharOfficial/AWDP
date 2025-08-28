// Serverless function for testimonials
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

  if (req.method !== 'GET') {
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

    // Fetch testimonials
    const [testimonials] = await connection.execute(
      'SELECT * FROM testimonials WHERE is_active = 1 ORDER BY is_featured DESC, created_at DESC'
    );

    await connection.end();

    // Send response
    res.status(200).json({
      success: true,
      data: testimonials
    });

  } catch (error) {
    console.error('Database error:', error);
    
    res.status(500).json({
      success: false,
      message: 'Error fetching testimonials',
      data: []
    });
  }
}
