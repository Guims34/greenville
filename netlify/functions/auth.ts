import { Handler } from '@netlify/functions';
import { PrismaClient } from '@prisma/client';
import * as jwt from 'jsonwebtoken';

const prisma = new PrismaClient();

const handler: Handler = async (event, context) => {
  if (event.httpMethod !== 'POST') {
    return {
      statusCode: 405,
      body: JSON.stringify({ error: 'Method not allowed' })
    };
  }

  try {
    const { email, username } = JSON.parse(event.body || '{}');

    const user = await prisma.user.upsert({
      where: { email },
      update: {},
      create: {
        email,
        username,
        level: 1,
        coins: 1000,
        premiumCoins: 0
      }
    });

    const token = jwt.sign(
      { userId: user.id },
      process.env.JWT_SECRET || 'your-secret-key',
      { expiresIn: '24h' }
    );

    return {
      statusCode: 200,
      body: JSON.stringify({ user, token })
    };
  } catch (error) {
    console.error('Auth error:', error);
    return {
      statusCode: 500,
      body: JSON.stringify({ error: 'Internal server error' })
    };
  }
}

export { handler };