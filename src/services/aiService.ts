// Email Service - Simplified
import axios from 'axios';

const API_URL = import.meta.env.DEV ? 'http://localhost:5173/mailer.php' : '/mailer.php';

export const sendEmailViaProxy = async (params: any) => {
  try {
    const response = await axios.post(API_URL, {
      action: 'send',
      ...params
    });

    if (response.data.error) throw new Error(response.data.error);
    return response.data;
  } catch (error) {
    console.error('PHP Proxy Send Error:', error);
    throw error;
  }
};
