import { describe, expect, it } from 'vitest';
import {
  decryptData,
  encryptData,
  exportKey,
  generateSecureKey,
  importKey,
} from '../../utils/cryptoUtils';

describe('cryptoUtils - AES-GCM Engine', () => {
  it('should generate a secure crypto key', async () => {
    const key = await generateSecureKey();
    expect(key).toBeDefined();
    expect(key.type).toBe('secret');
  });

  it('should export and import a key as hex correctly', async () => {
    const key = await generateSecureKey();
    const hex = await exportKey(key);

    expect(hex).toMatch(/^[0-9a-f]{64}$/); // 256-bit key = 64 hex chars

    const importedKey = await importKey(hex);
    expect(importedKey).toBeDefined();
    expect(importedKey.type).toBe('secret');
  });

  it('should encrypt and decrypt data correctly', async () => {
    const key = await generateSecureKey();
    const secretMessage = 'Top Secret: Black-Ops Mission';

    const { iv, ciphertext } = await encryptData(secretMessage, key);

    expect(iv).toBeDefined();
    expect(ciphertext).toBeDefined();
    expect(ciphertext).not.toBe(secretMessage);

    const decrypted = await decryptData(ciphertext, iv, key);
    expect(decrypted).toBe(secretMessage);
  });

  it('should fail to decrypt with the wrong key', async () => {
    const key1 = await generateSecureKey();
    const key2 = await generateSecureKey();
    const secretMessage = 'Classified Info';

    const { iv, ciphertext } = await encryptData(secretMessage, key1);

    await expect(decryptData(ciphertext, iv, key2)).rejects.toThrow();
  });

  it('should fail to decrypt with corrupted ciphertext', async () => {
    const key = await generateSecureKey();
    const { iv, ciphertext } = await encryptData('Valid Data', key);

    const corruptedCiphertext = ciphertext.substring(0, ciphertext.length - 4) + 'AAAA';

    await expect(decryptData(corruptedCiphertext, iv, key)).rejects.toThrow();
  });
});
