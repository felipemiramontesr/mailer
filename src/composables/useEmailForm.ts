import { computed, ref } from 'vue';
import { refineMessage, sendEmailViaProxy, storeSignal } from '../services/aiService';
import type {
  AIRefinementAction,
  EmailFormData,
  EmailPayload,
  SecurityState,
  SendStatus,
} from '../types';
import { encryptData, exportKey, generateSecureKey } from '../utils/cryptoUtils';
import { generateEmailTemplate } from '../utils/emailTemplate';

import { useSystemLog } from './useSystemLog';
import { useToasts } from './useToasts';

/**
 * Composable for managing Email Form behavior and state.
 * Encapsulates form data, security logic, and API transmission sequence.
 */
export function useEmailForm() {
  const { toasts, addToast, removeToast } = useToasts();
  const { logs, addLog, clearLogs } = useSystemLog();

  // --- State ---
  const form = ref<EmailFormData>({
    clientName: 'B. Eng. Felipe de Jesús Miramontes Romero',
    clientEmail: '',
    subject: '',
    message: '',
  });

  const security = ref<SecurityState>({
    password: '',
    authCode: '',
    showPinField: false,
    blackOpsMode: false,
    burnTimer: 10,
  });

  const isSending = ref(false);
  const sendStatus = ref<SendStatus>('idle');
  const errorMessage = ref('');
  const showPassword = ref(false);
  const showPreview = ref(false);

  // Persistence for Black-Ops Handshake
  const pendingBody = ref('');
  const pendingSubject = ref('');

  // --- Computed ---
  const emailTemplateHTML = computed(() => generateEmailTemplate(form.value));

  const isFormValid = computed(() => {
    const basicValid = !!(form.value.message && form.value.subject);
    const authValid = sendStatus.value === 'awaiting_2fa' ? !!security.value.authCode : true;
    return basicValid && authValid && !isSending.value && sendStatus.value !== 'success';
  });

  // --- Methods ---

  /**
   * Resets the form to initial state after successful transmission
   */
  const resetForm = () => {
    form.value.subject = '';
    form.value.message = '';
    security.value.password = '';
    security.value.authCode = '';
    security.value.showPinField = false;
    security.value.blackOpsMode = false;
    security.value.burnTimer = 10;
    pendingBody.value = '';
    pendingSubject.value = '';
  };

  /**
   * Handles the primary email transmission sequence
   */
  const handleSubmit = async () => {
    if (!isFormValid.value) return;

    isSending.value = true;
    sendStatus.value = 'sending';

    try {
      let body = emailTemplateHTML.value;
      let subject = form.value.subject;

      if (security.value.blackOpsMode) {
        if (!security.value.authCode) {
          addLog('CRYPTO', 'INITIATING_ZERO_KNOWLEDGE_SEQUENCE');
          const key = await generateSecureKey();
          const keyHex = await exportKey(key);
          const { iv, ciphertext } = await encryptData(body, key);

          const signalId = Math.random().toString(36).substring(2, 12);
          addLog('CRYPTO', `ENCRPYTION_COMPLETE: ID=${signalId.toUpperCase()}`);

          addLog('NETWORK', 'STORING_EPHEMERAL_SIGNAL');
          await storeSignal(
            signalId,
            iv,
            ciphertext,
            security.value.password,
            security.value.burnTimer
          );

          const portalUrl = `${window.location.origin}/portal.html?id=${signalId}#${keyHex}`;
          pendingSubject.value = `[Secure Signal] ${subject}`;
          pendingBody.value = `
                    <div style="font-family: 'Inter', sans-serif; background: #030a16; color: #fff; padding: 40px; text-align: center; border: 1px solid rgba(0, 247, 255, 0.2);">
                        <h2 style="color: #00f7ff; letter-spacing: 2px;">Incoming Secure Signal</h2>
                        <p style="color: rgba(255,255,255,0.7); margin-bottom: 30px;">A high-priority encrypted transmission has been registered for your node.</p>
                        <a href="${portalUrl}" style="background: transparent; color: #00f7ff; border: 1px solid #00f7ff; padding: 12px 24px; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; font-size: 14px;">Access Secure Portal</a>
                        <p style="font-size: 10px; color: rgba(255,255,255,0.3); margin-top: 40px;">SIGNAL_REF: ${signalId.toUpperCase()} // BURN_AFTER_READING_ACTIVE</p>
                    </div>
                `;
        }

        subject = pendingSubject.value;
        body = pendingBody.value;
      }

      const payload: EmailPayload = {
        to_name: form.value.clientName,
        to_email: form.value.clientEmail,
        subject,
        body,
        burn_timer: security.value.blackOpsMode ? security.value.burnTimer : undefined,
      };

      addLog('NETWORK', 'READY_FOR_TRANSMISSION');
      const result = await sendEmailViaProxy(
        payload,
        security.value.password,
        security.value.authCode
      );

      if (result.status === '2fa_required') {
        addLog('AUTH', '2FA_CHALLENGE_ISSUED');
        addToast('Security PIN Required', 'warning');
        security.value.showPinField = true;
        sendStatus.value = 'awaiting_2fa';
        return;
      }

      // Success Handler
      addLog('SYSTEM', 'TRANSMISSION_SUCCESS');
      addToast('Signal Transmitted Successfully', 'success');
      sendStatus.value = 'success';
      resetForm();

      setTimeout(() => {
        sendStatus.value = 'idle';
      }, 4000);
    } catch (error: any) {
      const errorMsg =
        error.response?.data?.error || error.message || 'System error during transmission';
      console.error('[Transmission Core Error]:', error);
      addLog('NETWORK', `FAILED: ${errorMsg.toUpperCase()}`);
      addToast(errorMsg, 'error');

      sendStatus.value = 'error';
      errorMessage.value = errorMsg;

      setTimeout(() => {
        sendStatus.value = 'idle';
        errorMessage.value = '';
      }, 6000);
    } finally {
      isSending.value = false;
    }
  };

  /**
   * AI Refinement Logic (v7.2)
   */
  const isRefining = ref(false);
  const handleAIRefine = async (action: AIRefinementAction, customCommand?: string) => {
    if (!form.value.message) {
      addToast('No content to optimize', 'warning');
      return;
    }

    isRefining.value = true;
    addLog('AI', `SIGNAL_ANALYSIS_START: ACTION=${action.toUpperCase()}`);

    try {
      const result = await refineMessage(
        form.value.message,
        action,
        security.value.password,
        customCommand
      );

      if (result.result) {
        form.value.message = result.result;
        addLog('AI', 'OPTIMIZATION_COMPLETE: SIGNAL_OVERWRITTEN');
        addToast('Signal Refined Successfully', 'success');
      } else if (result.error) {
        throw new Error(result.error);
      }
    } catch (error: any) {
      const msg = error.response?.data?.error || error.message || 'AI processing failure';
      addLog('AI', `CRITICAL_ERROR: ${msg.toUpperCase()}`);
      addToast(msg, 'error');
    } finally {
      isRefining.value = false;
    }
  };

  return {
    form,
    security,
    isSending,
    sendStatus,
    errorMessage,
    showPassword,
    showPreview,
    emailTemplateHTML,
    isFormValid,
    handleSubmit,
    handleAIRefine,
    isRefining,
    toasts,
    removeToast,
    logs,
    clearLogs,
  };
}
