import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import EmailForm from '../../components/EmailForm.vue';
import { sendEmailViaProxy } from '../../services/aiService';

// Mock lucide icons
vi.mock('lucide-vue-next', () => ({
  Mail: { name: 'Mail' },
  ShieldAlert: { name: 'ShieldAlert' },
  Satellite: { name: 'Satellite' },
  Send: { name: 'Send' },
  ShieldCheck: { name: 'ShieldCheck' },
  Type: { name: 'Type' },
  Loader2: { name: 'Loader2' },
  CheckCircle2: { name: 'CheckCircle2' },
  AlertCircle: { name: 'AlertCircle' },
  Eye: { name: 'Eye' },
  EyeOff: { name: 'EyeOff' },
  Layout: { name: 'Layout' },
  X: { name: 'X' },
}));

// Mock the AI service
vi.mock('../../services/aiService', () => ({
  sendEmailViaProxy: vi.fn(),
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

  it('disables send button when subject or message is empty', () => {
    const wrapper = mount(EmailForm);
    const sendButton = wrapper.find('button.send-btn');

    // Initial state is empty
    expect(sendButton.attributes('disabled')).toBeDefined();
  });

  it('enables send button when form is filled', async () => {
    const wrapper = mount(EmailForm);
    const inputs = wrapper.findAll('input, textarea');

    // Fill subject and message (usually indices 2 and 3 based on structure)
    // Using v-model bindings via wrapper.setValue
    await wrapper.find('input[placeholder*="subject"]').setValue('Project Update');
    await wrapper.find('textarea').setValue('This is a test message');

    const sendButton = wrapper.find('button.send-btn');
    expect(sendButton.attributes('disabled')).toBeUndefined();
  });

  it('shows password field initially and 2FA field after 2fa_required response', async () => {
    const wrapper = mount(EmailForm);

    // Should show password input initially
    expect(wrapper.find('input[type="password"]').exists()).toBe(true);
    expect(wrapper.find('.pin-input').exists()).toBe(false);

    // Mock 2FA required response
    (sendEmailViaProxy as any).mockResolvedValueOnce({ status: '2fa_required' });

    // Fill form to enable button
    await wrapper.find('input[placeholder*="subject"]').setValue('Trial');
    await wrapper.find('textarea').setValue('Message');
    await wrapper.find('input[placeholder*="password"]').setValue('master123');

    await wrapper.find('form').trigger('submit.prevent');

    // After submit and 2FA response, should show PIN field
    expect(wrapper.find('.pin-input').exists()).toBe(true);
  });
});
