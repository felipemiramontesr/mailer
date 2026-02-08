// Email Service - Black Ops v6
import axios from 'axios';

const API_URL = import.meta.env.DEV ? 'http://localhost:5173/mailer.php' : '/mailer.php';

/**
 * Injects randomized noise into the payload to break pattern analysis (Polymorphic Traffic)
 */
const injectNoise = (payload: any) => {
  const noiseSize = Math.floor(Math.random() * 64) + 32;
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+';
  let noise = '';
  for (let i = 0; i < noiseSize; i++)
    noise += chars.charAt(Math.floor(Math.random() * chars.length));
  return { ...payload, _ntp_noise: noise, _entropy: Math.random().toString(36).substring(7) };
};

export const sendEmailViaProxy = async (params: any, password?: string, authCode?: string) => {
  try {
    const payload = injectNoise({
      action: 'send',
      password,
      auth_code: authCode,
      ...params,
    });

    const response = await axios.post(API_URL, payload);
    if (response.data.error) throw new Error(response.data.error);
    return response.data;
  } catch (error) {
    console.error('PHP Proxy Send Error:', error);
    throw error;
  }
};

export const storeSignal = async (id: string, iv: string, blob: string, password?: string) => {
  const response = await axios.post(
    API_URL,
    injectNoise({ action: 'store_signal', id, iv, blob, password })
  );
  return response.data;
};

export const fetchSignal = async (id: string) => {
  const response = await axios.post(API_URL, injectNoise({ action: 'fetch_signal', id }));
  return response.data;
};

export const shredSignal = async (id: string) => {
  const response = await axios.post(API_URL, injectNoise({ action: 'shred_signal', id }));
  return response.data;
};
