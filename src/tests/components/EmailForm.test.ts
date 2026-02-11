import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import EmailForm from '../../components/EmailForm.vue';
import { sendEmailViaProxy } from '../../services/aiService';

// Mock lucide icons
vi.mock('lucide-vue-next', () => ({
  AlertCircle: { name: 'AlertCircle' },
  CheckCircle2: { name: 'CheckCircle2' },
  Eye: { name: 'Eye' },
  EyeOff: { name: 'EyeOff' },
  Languages: { name: 'Languages' },
  Layout: { name: 'Layout' },
  Loader2: { name: 'Loader2' },
  Mail: { name: 'Mail' },
  Satellite: { name: 'Satellite' },
  Send: { name: 'Send' },
  ShieldAlert: { name: 'ShieldAlert' },
  ShieldCheck: { name: 'ShieldCheck' },
  Sparkles: { name: 'Sparkles' },
  Type: { name: 'Type' },
  Wand2: { name: 'Wand2' },
  X: { name: 'X' },
}));

// Mock the AI service
vi.mock('../../services/aiService', () => ({
  sendEmailViaProxy: vi.fn(),
  refineMessage: vi.fn(),
}));

describe('EmailForm.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders correctly with initial state', () => {
    const wrapper = mount(EmailForm);
    expect(wrapper.find('h2.glow-text').text()).toBe('felipemiramontesr.net');
    expect(wrapper.find('button.send-btn').text()).toContain('INITIALIZE SEND SEQUENCE');
  });

  it('toggles preview when LP button is clicked', async () => {
    const wrapper = mount(EmailForm);
    const lpButton = wrapper.find('.preview-toggle');

    expect(wrapper.find('.live-preview-container').exists()).toBe(false);

    await lpButton.trigger('click');
    expect(wrapper.find('.live-preview-container').exists()).toBe(true);
    expect(lpButton.classes()).toContain('active');
  });

  it('keeps send button enabled to allow validation toasts', () => {
    const wrapper = mount(EmailForm);
    const sendButton = wrapper.find('button.send-btn');

    // Button should be enabled to show Error Toast on click
    expect(sendButton.attributes('disabled')).toBeUndefined();
  });

  it('enables send button when form is filled', async () => {
    const wrapper = mount(EmailForm);
    // This test is less relevant now as button is always enabled, but ensures no regression
    const sendButton = wrapper.find('button.send-btn');
    expect(sendButton.attributes('disabled')).toBeUndefined();
  });

  it('shows password field initially and 2FA field after 2fa_required response', async () => {
    const wrapper = mount(EmailForm);

    // Should show password input initially
    expect(wrapper.find('input[type="password"]').exists()).toBe(true);
    expect(wrapper.find('.pin-input').exists()).toBe(false);

    // Mock 2FA required response (needs to be mocked before click)
    (sendEmailViaProxy as any).mockResolvedValueOnce({ status: '2fa_required' });

    // Fill form to pass validation
    await wrapper.find('input[type="text"][readonly]').setValue('Client'); // Client Name is readonly
    await wrapper.find('input[type="email"]').setValue('test@example.com'); // Must replace empty email
    await wrapper.find('input[placeholder*="subject"]').setValue('Trial');
    await wrapper.find('textarea').setValue('Message');
    await wrapper.find('input[placeholder*="password"]').setValue('master123');

    // Trigger click instead of submit
    await wrapper.find('button.send-btn').trigger('click');

    // Wait for async operations
    await new Promise((resolve) => setTimeout(resolve, 0));

    // After submit and 2FA response, should show PIN field
    expect(wrapper.find('.pin-input').exists()).toBe(true);
  });
});
