/**
 * Crypto Engine v4 (Black-Ops)
 * Implements AES-GCM 256-bit encryption for ZK-Delivery
 */

/**
 * Generates a high-entropy key for AES-GCM
 */
export async function generateSecureKey(): Promise<CryptoKey> {
  return window.crypto.subtle.generateKey({ name: 'AES-GCM', length: 256 }, true, [
    'encrypt',
    'decrypt',
  ]);
}

/**
 * Exports a key to a hex string for the URL fragment
 */
export async function exportKey(key: CryptoKey): Promise<string> {
  const raw = await window.crypto.subtle.exportKey('raw', key);
  return Array.from(new Uint8Array(raw))
    .map((b) => b.toString(16).padStart(2, '0'))
    .join('');
}

/**
 * Imports a key from a hex string
 */
export async function importKey(hex: string): Promise<CryptoKey> {
  const bytes = new Uint8Array(hex.match(/.{1,2}/g)!.map((byte) => parseInt(byte, 16)));
  return window.crypto.subtle.importKey('raw', bytes, 'AES-GCM', true, ['encrypt', 'decrypt']);
}

/**
 * Encrypts data using AES-GCM
 */
export async function encryptData(
  data: string,
  key: CryptoKey
): Promise<{ iv: string; ciphertext: string }> {
  const iv = window.crypto.getRandomValues(new Uint8Array(12));
  const encoded = new TextEncoder().encode(data);
  const ciphertext = await window.crypto.subtle.encrypt({ name: 'AES-GCM', iv }, key, encoded);

  return {
    iv: Array.from(iv)
      .map((b) => b.toString(16).padStart(2, '0'))
      .join(''),
    ciphertext: btoa(String.fromCharCode(...new Uint8Array(ciphertext))),
  };
}

/**
 * Decrypts data using AES-GCM
 */
export async function decryptData(
  ciphertext: string,
  ivHex: string,
  key: CryptoKey
): Promise<string> {
  const iv = new Uint8Array(ivHex.match(/.{1,2}/g)!.map((byte) => parseInt(byte, 16)));
  const binary = atob(ciphertext);
  const encrypted = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) encrypted[i] = binary.charCodeAt(i);

  const decrypted = await window.crypto.subtle.decrypt({ name: 'AES-GCM', iv }, key, encrypted);

  return new TextDecoder().decode(decrypted);
}
