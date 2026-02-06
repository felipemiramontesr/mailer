<script setup lang="ts">
import { ref } from 'vue';
import { Mail, Send, User, AtSign, Type, Loader2, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { sendEmailViaProxy } from '../services/aiService';

const form = ref({
  clientName: 'B. Eng. Felipe de Jesús Miramontes Romero',
  clientEmail: 'info@felipemiramontesr.net',
  subject: '',
  message: '',
});

const isSending = ref(false);
const sendStatus = ref<'idle' | 'sending' | 'success' | 'error'>('idle');

const sendEmail = async () => {
  if (!form.value.message || !form.value.subject) {
    return;
  }

  isSending.value = true;
  sendStatus.value = 'sending';
  
  try {
    const templateParams = {
      to_name: form.value.clientName,
      to_email: form.value.clientEmail,
      subject: form.value.subject,
      body: `
        <div style="background-color: #080b1a; color: #ffffff; padding: 40px; font-family: 'Inter', Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #00f7ff33; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 247, 255, 0.1);">
          <!-- Header -->
          <div style="border-bottom: 2px solid #00f7ff; padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
            <h1 style="color: #00f7ff; font-family: 'Orbitron', sans-serif; text-transform: uppercase; letter-spacing: 4px; margin: 0; font-size: 24px;">Secure Transmission</h1>
          </div>
          
          <!-- Content Case -->
          <div style="background: rgba(17, 22, 51, 0.6); padding: 25px; border-radius: 8px; border-left: 4px solid #00f7ff;">
            <p style="margin-top: 0; color: #00f7ff; font-weight: bold; font-size: 14px; text-transform: uppercase;">[ Sender Identified ]</p>
            <p style="color: #e0e0f0; font-size: 16px; margin: 5px 0 20px 0;">${form.value.clientName}</p>
            
            <p style="color: #00f7ff; font-weight: bold; font-size: 14px; text-transform: uppercase;">[ Subject ]</p>
            <p style="color: #ffffff; font-size: 18px; font-weight: 600; margin: 5px 0 25px 0;">${form.value.subject}</p>
            
            <div style="color: #cbd5e1; line-height: 1.7; font-size: 16px; border-top: 1px solid rgba(0, 247, 255, 0.1); padding-top: 20px;">
              ${form.value.message.replace(/\n/g, '<br>')}
            </div>
          </div>
          
          <!-- Footer -->
          <div style="margin-top: 40px; text-align: center; border-top: 1px solid rgba(0, 247, 255, 0.1); padding-top: 20px;">
            <p style="color: #5b6ea3; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; margin: 0;">NAVY TECH DESIGN - SECURITY BRIDGE v2.0</p>
            <p style="color: #33446b; font-size: 10px; margin-top: 8px;">Encryption: AES-256 | Source: mailer.felipemiramontesr.net</p>
          </div>
        </div>
      `
    };

    await sendEmailViaProxy(templateParams);
    
    // Success Phase
    sendStatus.value = 'success';
    form.value.subject = '';
    form.value.message = '';

    // Reset button after feedback
    setTimeout(() => {
      sendStatus.value = 'idle';
    }, 4000);

  } catch (error) {
    console.error('Email send failed:', error);
    sendStatus.value = 'error';
    setTimeout(() => {
      sendStatus.value = 'idle';
    }, 4000);
  } finally {
    isSending.value = false;
  }
};
</script>

<template>
  <div class="tech-card email-form-container">
    <div class="form-header">
      <h2 class="glow-text">Secure Mailer</h2>
      <div class="status-indicator">
        <span class="dot"></span> SECURE CONNECTION
      </div>
    </div>

    <form @submit.prevent="sendEmail" class="main-form" autocomplete="off">
      <div class="input-row">
        <div class="input-group">
          <label><User :size="14" /> Sender Name</label>
          <input v-model="form.clientName" type="text" placeholder="Your Name" required autocomplete="off" />
        </div>
        <div class="input-group">
          <label><AtSign :size="14" /> Sender Email</label>
          <input v-model="form.clientEmail" type="email" placeholder="your@email.com" required autocomplete="off" />
        </div>
      </div>

      <div class="input-group">
        <label><Type :size="14" /> Subject</label>
        <input v-model="form.subject" type="text" placeholder="Project Alpha - Phase 1" required />
      </div>

      <div class="message-section">
        <label><Mail :size="14" /> Message Content</label>
        <textarea 
          v-model="form.message" 
          rows="8" 
          placeholder="Escribe tu mensaje aquí..." 
          required
        ></textarea>
      </div>

      <button 
        type="submit" 
        class="cyan-btn send-btn" 
        :class="sendStatus"
        :disabled="isSending || sendStatus === 'success'"
      >
        <template v-if="sendStatus === 'idle'">
          <Send :size="20" /> 
          INITIALIZE SEND SEQUENCE
        </template>
        
        <template v-else-if="sendStatus === 'sending'">
          <Loader2 :size="20" class="spin" />
          DECRYPTING & SENDING...
        </template>
        
        <template v-else-if="sendStatus === 'success'">
          <CheckCircle2 :size="20" />
          TRANSMISSION COMPLETE ✅
        </template>
        
        <template v-else-if="sendStatus === 'error'">
          <AlertCircle :size="20" />
          TRANSMISSION ERROR ⚠️
        </template>
      </button>
    </form>
  </div>
</template>

<style scoped>
.email-form-container {
  max-width: 700px;
  margin: 0 auto;
  text-align: left;
}

.input-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--border-color);
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.65rem;
  color: var(--text-secondary);
  letter-spacing: 1px;
  background: rgba(0, 247, 255, 0.05);
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  border: 1px solid rgba(0, 247, 255, 0.1);
}

.dot {
  width: 6px;
  height: 6px;
  background-color: var(--accent);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--accent-glow);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 0.4; }
  50% { opacity: 1; }
  100% { opacity: 0.4; }
}

.main-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 1px;
}

input, textarea {
  width: 100%;
  background: rgba(8, 11, 26, 0.5);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  padding: 0.8rem;
  color: white;
  transition: all 0.3s ease;
  font-family: 'Inter', sans-serif;
}

input:focus, textarea:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 15px rgba(0, 247, 255, 0.15);
  background: rgba(17, 22, 51, 0.8);
}

.send-btn {
  width: 100%;
  margin-top: 1rem;
  padding: 1.2rem;
  font-size: 1rem;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.send-btn.success {
  background: rgba(0, 247, 255, 0.1);
  border-color: #00ff88;
  color: #00ff88;
  box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
}

.send-btn.error {
  border-color: #ff4444;
  color: #ff4444;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@media (max-width: 600px) {
  .input-row {
    grid-template-columns: 1fr;
  }
}
</style>
