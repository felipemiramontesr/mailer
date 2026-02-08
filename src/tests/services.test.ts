import axios from 'axios';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { sendEmailViaProxy } from '../services/aiService';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

describe('aiService - sendEmailViaProxy', () => {
  beforeEach(() => {
    mockedAxios.post.mockReset();
  });

  const mockParams = {
    to_name: 'Felipe',
    to_email: 'felipe@example.com',
    subject: 'Test',
    body: '<html></html>',
  };

  it('should send a POST request with correct parameters', async () => {
    mockedAxios.post.mockResolvedValueOnce({ data: { status: 'success' } });

    const result = await sendEmailViaProxy(mockParams, 'pass123', '123456');

    expect(mockedAxios.post).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        action: 'send',
        password: 'pass123',
        auth_code: '123456',
        ...mockParams,
      })
    );
    expect(result.status).toBe('success');
  });

  it('should handle 2fa_required status', async () => {
    mockedAxios.post.mockResolvedValueOnce({ data: { status: '2fa_required' } });

    const result = await sendEmailViaProxy(mockParams, 'pass123');

    expect(result.status).toBe('2fa_required');
  });

  it('should throw an error if the response contains an error field', async () => {
    mockedAxios.post.mockResolvedValueOnce({ data: { error: 'Invalid password' } });

    await expect(sendEmailViaProxy(mockParams, 'wrong')).rejects.toThrow('Invalid password');
  });

  it('should throw an error if the axios request fails', async () => {
    mockedAxios.post.mockRejectedValueOnce(new Error('Network Error'));

    await expect(sendEmailViaProxy(mockParams)).rejects.toThrow('Network Error');
  });
});
