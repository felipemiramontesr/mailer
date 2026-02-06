import axios from 'axios';

// The PHP API endpoint (In dev, we call the localhost / public / mailer.php)
// In production, this will be /mailer.php
const API_URL = import.meta.env.DEV ? 'http://localhost:5173/mailer.php' : '/mailer.php';

export const polishMessage = async (message: string, isEnglish: boolean) => {
  const prompt = isEnglish
    ? `Refine this email message for a client to sound more professional and technical (Navy Tech style), keeping it concise and polite: "${message}"`
    : `Pule este mensaje de correo electrónico para un cliente para que suene más profesional y técnico (estilo Navy Tech), manteniéndolo conciso y educado: "${message}"`;

  try {
    const response = await axios.post(API_URL, {
      action: 'polish',
      message: message,
      prompt: prompt
    });

    if (response.data.error) throw new Error(response.data.error);
    return response.data.result;
  } catch (error) {
    console.error('PHP Proxy Polish Error:', error);
    throw error;
  }
};

export const translateMessage = async (message: string, toEnglish: boolean) => {
  const prompt = toEnglish
    ? `Translate the following message from Spanish to professional English for a client: "${message}"`
    : `Translate the following message from English to professional Spanish for a client: "${message}"`;

  try {
    const response = await axios.post(API_URL, {
      action: 'translate',
      message: message,
      prompt: prompt
    });

    if (response.data.error) throw new Error(response.data.error);
    return response.data.result;
  } catch (error) {
    console.error('PHP Proxy Translate Error:', error);
    throw error;
  }
};

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
