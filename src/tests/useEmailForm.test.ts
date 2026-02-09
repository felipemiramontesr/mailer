import { beforeEach, describe, expect, it, vi } from 'vitest';
import { useEmailForm } from '../composables/useEmailForm';
import { sendEmailViaProxy, storeSignal } from '../services/aiService';

// Mock dependencies
vi.mock('../services/aiService', () => ({
  sendEmailViaProxy: vi.fn(),
  storeSignal: vi.fn(),
  refineMessage: vi.fn(),
}));

vi.mock('../utils/cryptoUtils', () => ({
  generateSecureKey: vi.fn().mockResolvedValue({}),
  exportKey: vi.fn().mockResolvedValue('fake-key-hex'),
  encryptData: vi.fn().mockResolvedValue({ iv: 'fake-iv', ciphertext: 'fake-cipher' }),
}));

vi.mock('../utils/emailTemplate', () => ({
  generateEmailTemplate: vi.fn().mockReturnValue('<html>Test Template</html>'),
}));

vi.mock('../composables/useToasts', () => ({
  useToasts: () => ({
    addToast: vi.fn(),
    removeToast: vi.fn(),
    toasts: [],
  }),
}));

describe('useEmailForm Composable', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should initialize with correct default state', () => {
    const { form, security, sendStatus } = useEmailForm();
    expect(form.value.clientName).toBe('B. Eng. Felipe de Jesús Miramontes Romero');
    expect(security.value.blackOpsMode).toBeFalsy();
    expect(sendStatus.value).toBe('idle');
  });

  it('should handle successful normal transmission', async () => {
    const { form, security, handleSubmit, sendStatus } = useEmailForm();
    form.value.clientEmail = 'test@example.com';
    form.value.subject = 'Hello';
    form.value.message = 'World';
    security.value.password = 'pass123';

    (sendEmailViaProxy as any).mockResolvedValueOnce({ success: true });

    await handleSubmit();

    expect(sendEmailViaProxy).toHaveBeenCalledWith(
      expect.objectContaining({ to_email: 'test@example.com' }),
      'pass123',
      ''
    );
    expect(sendStatus.value).toBe('success');
  });

  it('should transition to awaiting_2fa when service requires it', async () => {
    const { form, security, handleSubmit, sendStatus } = useEmailForm();
    security.value.password = 'pass123';
    form.value.subject = 'Secret';
    form.value.message = 'Data';

    (sendEmailViaProxy as any).mockResolvedValueOnce({ status: '2fa_required' });

    await handleSubmit();

    expect(sendStatus.value).toBe('awaiting_2fa');
    expect(security.value.showPinField).toBe(true);
  });

  it('should persist secure portal link during Black-Ops 2FA handshake', async () => {
    const { form, security, handleSubmit, sendStatus } = useEmailForm();

    // 1. Setup Black-Ops Mode
    security.value.blackOpsMode = true;
    security.value.password = 'master-key';
    form.value.clientEmail = 'recipient@example.com';
    form.value.subject = 'Classified';
    form.value.message = 'Payload';

    // 2. First attempt (Dispatch PIN)
    (sendEmailViaProxy as any).mockResolvedValueOnce({ status: '2fa_required' });
    (storeSignal as any).mockResolvedValueOnce({ status: 'stored' });

    await handleSubmit();

    // Verify crypto was called
    expect(storeSignal).toHaveBeenCalled();
    expect(sendEmailViaProxy).toHaveBeenCalledWith(
      expect.objectContaining({
        body: expect.stringContaining('Access Secure Portal'),
      }),
      'master-key',
      ''
    );
    expect(sendStatus.value).toBe('awaiting_2fa');

    // 3. Second attempt (Submit PIN)
    // IMPORTANT: It should NOT re-encrypt or re-store the signal
    vi.clearAllMocks();
    (sendEmailViaProxy as any).mockResolvedValueOnce({ success: true });
    security.value.authCode = '123456';

    await handleSubmit();

    expect(storeSignal).not.toHaveBeenCalled(); // Critical check for persistence fix
    expect(sendEmailViaProxy).toHaveBeenCalledWith(
      expect.objectContaining({
        body: expect.stringContaining('Access Secure Portal'),
        to_email: 'recipient@example.com',
      }),
      'master-key',
      '123456'
    );
    expect(sendStatus.value).toBe('success');
  });
});
