<script setup lang="ts">
import { ref } from 'vue';
import { Mail, Send, ShieldCheck, Satellite, Type, Loader2, CheckCircle2, AlertCircle } from 'lucide-vue-next';
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
        <div style="background-color: #080b1a; color: #ffffff; padding: 40px; font-family: 'Inter', Arial, sans-serif; max-width: 600px; margin: 40px auto; border: 1px solid #00f7ff33; border-radius: 16px; box-shadow: 0 0 50px rgba(0, 247, 255, 0.15);">
          
          <!-- HUD System Header -->
          <div style="border-bottom: 1px solid rgba(0, 247, 255, 0.3); padding-bottom: 20px; margin-bottom: 40px; display: table; width: 100%;">
            <div style="display: table-cell; vertical-align: middle;">
              <span style="color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 11px; letter-spacing: 2px; font-weight: bold;">[ ● ONLINE ]</span>
              <span style="color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 11px; letter-spacing: 2px; font-weight: bold; margin-left: 15px; opacity: 0.7;">[ ● SECURE_AUTH ]</span>
            </div>
            <div style="display: table-cell; text-align: right; vertical-align: middle; color: #5b6ea3; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 1px;">
              TIMESTAMP: ${new Date().toISOString().split('T')[0]} | V3.0
            </div>
          </div>

          <!-- Component Interface -->
          <div style="background: rgba(17, 22, 51, 0.4); padding: 35px; border-radius: 12px; border: 1px solid rgba(0, 247, 255, 0.1); position: relative;">
            
            <!-- Link Origin -->
            <div style="margin-bottom: 30px;">
              <div style="margin-bottom: 8px;">
                <span style="display: inline-block; color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px;">👤 LINK_ORIGIN_VERIFIED</span>
              </div>
              <p style="margin: 0; color: #ffffff; font-size: 17px; font-weight: 500; border-left: 2px solid #00f7ff; padding-left: 15px;">
                B. Eng. Felipe de Jesús Miramontes Romero
              </p>
            </div>
            
            <!-- Transceiver Subject -->
            <div style="margin-bottom: 40px;">
              <div style="margin-bottom: 8px;">
                <span style="display: inline-block; color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px;">🛰️ TRANSCEIVER_SUBJECT</span>
              </div>
              <p style="margin: 0; color: #00f7ff; font-size: 20px; font-weight: 600; text-shadow: 0 0 10px rgba(0, 247, 255, 0.3); border-left: 2px solid #00f7ff; padding-left: 15px;">
                ${form.value.subject}
              </p>
            </div>
            
            <!-- Data Stream Section -->
            <div style="border-top: 1px solid rgba(0, 247, 255, 0.1); padding-top: 35px;">
              <div style="margin-bottom: 20px;">
                <span style="display: inline-block; color: #5b6ea3; font-family: 'Orbitron', sans-serif; font-size: 10px; letter-spacing: 3px;">📥 DECRYPTED_DATA_PACKETS</span>
              </div>
              <div style="color: #cbd5e1; line-height: 1.8; font-size: 16px; padding: 25px; background: rgba(0, 0, 0, 0.3); border-radius: 12px; border: 1px solid rgba(0, 247, 255, 0.15); box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.5);">
                <div style="color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 8px; margin-bottom: 15px; opacity: 0.5;">[ START_ENVELOPE ]</div>
                ${form.value.message.replace(/\n/g, '<br>')}
                <div style="color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 8px; margin-top: 25px; opacity: 0.5;">[ END_ENVELOPE ]</div>
              </div>
            </div>
          </div>
          
          <!-- Neural Link Footer -->
          <div style="margin-top: 50px; border-top: 1px solid rgba(0, 247, 255, 0.1); padding-top: 30px;">
            <div style="display: table; width: 100%;">
              <div style="display: table-cell; vertical-align: middle;">
                <p style="color: #00f7ff; font-size: 10px; letter-spacing: 4px; margin: 0; font-family: 'Orbitron', sans-serif; text-transform: uppercase;">🛡️ System Integrity Verified</p>
                <p style="color: #273452; font-size: 8px; margin-top: 8px; font-family: 'Orbitron', sans-serif;">Auth: SMTP_TLS_V1.3 | Node: mailer.felipemiramontesr.net</p>
              </div>
              <div style="display: table-cell; text-align: right; vertical-align: middle;">
                <div style="width: 8px; height: 8px; background: #00f7ff; border-radius: 50%; display: inline-block; box-shadow: 0 0 10px #00f7ff;"></div>
              </div>
            </div>
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
      <h2 class="glow-text">felipemiramontesr.net</h2>
      <div class="status-indicator">
        <span class="dot"></span> SECURE CONNECTION
      </div>
    </div>

    <form @submit.prevent="sendEmail" class="main-form" autocomplete="off">
      <div class="input-row">
        <div class="input-group icon-inside">
          <ShieldCheck :size="16" class="inner-icon" />
          <input v-model="form.clientName" type="text" readonly autocomplete="off" />
        </div>
        <div class="input-group icon-inside">
          <Satellite :size="16" class="inner-icon" />
          <input v-model="form.clientEmail" type="email" placeholder="Destinatario (Email)" required autocomplete="off" />
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

.icon-inside {
  position: relative;
}

.icon-inside .inner-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--accent);
  opacity: 0.8;
  pointer-events: none;
}

.icon-inside input {
  padding-left: 38px;
  cursor: default;
}

input[readonly] {
  opacity: 0.7;
  border-style: dashed;
  background: rgba(8, 11, 26, 0.3);
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
