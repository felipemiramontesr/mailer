<script setup lang="ts">
import { ref, computed } from 'vue';
import { Mail, Send, ShieldCheck, Satellite, Type, Loader2, CheckCircle2, AlertCircle, Eye, EyeOff, Layout } from 'lucide-vue-next';
import { sendEmailViaProxy } from '../services/aiService';
import { generateEmailTemplate } from '../utils/emailTemplate';

const form = ref({
  clientName: 'B. Eng. Felipe de Jesús Miramontes Romero',
  clientEmail: '',
  subject: '',
  message: '',
});

const isSending = ref(false);
const sendStatus = ref<'idle' | 'sending' | 'success' | 'error' | 'awaiting_2fa'>('idle');
const errorMessage = ref('');
const showPassword = ref(false);
const showPreview = ref(false);
const security = ref({
  password: '',
  authCode: '',
  showPinField: false
});


const emailTemplateHTML = computed(() => generateEmailTemplate(form.value));

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
      body: emailTemplateHTML.value
    };

    const result = await sendEmailViaProxy(templateParams, security.value.password, security.value.authCode);
    
    if (result.status === '2fa_required') {
      security.value.showPinField = true;
      sendStatus.value = 'awaiting_2fa';
      return;
    }

    // Success Phase
    sendStatus.value = 'success';
    form.value.subject = '';
    form.value.message = '';
    security.value.password = '';
    security.value.authCode = '';
    security.value.showPinField = false;

    setTimeout(() => {
      sendStatus.value = 'idle';
    }, 4000);

  } catch (error: any) {
    console.error('Email send failed:', error);
    sendStatus.value = 'error';
    errorMessage.value = error.response?.data?.error || error.message || 'Transmission failed';
    setTimeout(() => {
      sendStatus.value = 'idle';
      errorMessage.value = '';
    }, 6000);
  } finally {
    isSending.value = false;
  }
};
</script>

<template>
  <div class="tech-card email-form-container" :class="{ 'with-preview': showPreview }">
    <div class="form-header">
      <h2 class="glow-text">felipemiramontesr.net</h2>
      
      <div class="header-status-area">
        <div class="status-indicator">
          <span class="dot"></span> SECURE_CONNECTION
        </div>
        
        <button type="button" class="preview-toggle" @click="showPreview = !showPreview" :class="{ active: showPreview }">
          <Layout v-if="!showPreview" :size="14" />
          <EyeOff v-else :size="14" />
          LP
        </button>
      </div>
    </div>

    <div class="form-layout">
      <form @submit.prevent="sendEmail" class="main-form" autocomplete="off">
        <div class="input-row">
          <div class="input-group icon-inside">
            <ShieldCheck :size="16" class="inner-icon" />
            <input v-model="form.clientName" type="text" readonly autocomplete="off" />
          </div>
          <div class="input-group icon-inside">
            <Satellite :size="16" class="inner-icon" />
            <input v-model="form.clientEmail" type="email" placeholder="felipemiramontesr@gmail.com" required autocomplete="off" />
          </div>
        </div>

        <div class="input-group">
          <label><Type :size="14" /> SUBJECT</label>
          <input v-model="form.subject" type="text" placeholder="Insert a massage subject" required />
        </div>

        <div class="message-section">
          <label><Mail :size="14" /> MESSAGE CONTENT</label>
          <textarea 
            v-model="form.message" 
            rows="8" 
            placeholder="Input your message here..." 
            required
          ></textarea>
        </div>

        <!-- Security Shield Layer -->
        <div class="security-layer tech-card">
          <div v-if="!security.showPinField" class="input-group">
            <label><ShieldCheck :size="14" /> Master Access Key</label>
            <div class="input-with-eye">
              <input 
                v-model="security.password" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Enter secure password" 
                required 
              />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword">
                <Eye v-if="!showPassword" :size="18" />
                <EyeOff v-else :size="18" />
              </button>
            </div>
          </div>
          
          <div v-else class="input-group 2fa-group animate-in">
            <label><AlertCircle :size="14" color="#00f7ff" /> Insert a verification PIN</label>
            <input 
              v-model="security.authCode" 
              type="text" 
              placeholder="••••••" 
              maxlength="6"
              required 
              class="pin-input"
            />
          </div>
        </div>

        <button 
          type="submit" 
          class="cyan-btn send-btn" 
          :class="sendStatus"
          :disabled="isSending || sendStatus === 'success' || !form.message || !form.subject || (sendStatus === 'awaiting_2fa' && !security.authCode)"
        >
          <template v-if="sendStatus === 'idle'">
            <Send :size="20" /> 
            INITIALIZE SEND SEQUENCE
          </template>
          
          <template v-else-if="sendStatus === 'sending'">
            <Loader2 :size="20" class="spin" />
            DECRYPTING & SENDING...
          </template>

          <template v-else-if="sendStatus === 'awaiting_2fa'">
            <ShieldCheck :size="20" />
            VERIFY PIN & AUTHORIZE
          </template>
          
          <template v-else-if="sendStatus === 'success'">
            <CheckCircle2 :size="20" />
            TRANSMISSION COMPLETE ✅
          </template>
          
          <template v-else-if="sendStatus === 'error'">
            <AlertCircle :size="20" />
            {{ errorMessage || 'TRANSMISSION ERROR ⚠️' }}
          </template>
        </button>
      </form>

      <!-- Live HUD Preview Section -->
      <div v-if="showPreview" class="live-preview-container animate-in">
        <div class="preview-header">
          <div class="preview-badge">HUD_LIVE_FEED</div>
        </div>
        <div class="preview-scroll-area">
          <div class="email-canvas" v-html="emailTemplateHTML"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.email-form-container {
  max-width: 700px;
  margin: 0 auto;
  text-align: left;
  transition: max-width 0.4s ease;
}

.email-form-container.with-preview {
  max-width: 1300px;
}

.form-layout {
  display: flex;
  gap: 30px;
}

.form-layout > form {
  flex: 1;
  min-width: 0;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: nowrap;
  gap: 10px;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-color);
}

.form-header .glow-text {
  font-size: 1.1rem;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.header-status-area {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.preview-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(0, 247, 255, 0.05);
  border: 1px solid rgba(0, 247, 255, 0.2);
  color: var(--accent);
  padding: 4px 10px;
  border-radius: 4px;
  font-family: 'Orbitron', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 50px;
  justify-content: center;
}

.preview-toggle:hover {
  background: rgba(0, 247, 255, 0.15);
  box-shadow: 0 0 10px rgba(0, 247, 255, 0.2);
}

.preview-toggle.active {
  background: var(--accent);
  color: #080b1a;
  border-color: var(--accent);
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

/* Preview Styles */
.live-preview-container {
  flex: 1.5;
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(0, 247, 255, 0.2);
  border-radius: 12px;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: inset 0 0 40px rgba(0, 247, 255, 0.03);
  align-self: flex-start;
}

.preview-header {
  background: rgba(0, 247, 255, 0.05);
  padding: 8px 15px;
  border-bottom: 1px solid rgba(0, 247, 255, 0.1);
  display: flex;
  justify-content: flex-end;
}

.preview-badge {
  background: var(--accent);
  color: #080b1a;
  font-family: 'Orbitron', sans-serif;
  font-size: 0.6rem;
  padding: 2px 8px;
  border-radius: 2px;
  font-weight: 700;
  letter-spacing: 1px;
}

.preview-scroll-area {
  flex: 1;
  overflow: hidden;
  padding: 15px;
  display: flex;
  justify-content: center;
}

.preview-scroll-area::-webkit-scrollbar {
  width: 4px;
}

.preview-scroll-area::-webkit-scrollbar-thumb {
  background: var(--accent);
  border-radius: 10px;
}

.email-canvas {
  width: 1000px;
  background: transparent;
  zoom: 0.55;
  flex-shrink: 0;
}

.input-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.main-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
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

.security-layer {
  background: rgba(0, 247, 255, 0.03);
  padding: 1rem;
  border: 1px solid rgba(0, 247, 255, 0.1);
}

.input-with-eye {
  position: relative;
  display: flex;
  align-items: center;
}

.eye-btn {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  color: var(--accent);
  cursor: pointer;
  opacity: 0.6;
  transition: opacity 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.eye-btn:hover {
  opacity: 1;
}

.pin-input {
  font-size: 1.5rem;
  letter-spacing: 0.8rem;
  text-align: center;
  color: var(--accent);
  font-family: 'Orbitron', monospace;
  background: rgba(0, 0, 0, 0.2) !important;
  text-transform: uppercase;
}

.pin-input::placeholder {
  font-family: sans-serif;
  letter-spacing: 0.4rem;
  opacity: 0.3;
}

.animate-in {
  animation: slideIn 0.4s ease-out;
}

@keyframes slideIn {
  from { transform: translateY(10px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
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

@media (max-width: 1000px) {
  .form-layout {
    flex-direction: column;
  }
}

@media (max-width: 600px) {
  .input-row {
    grid-template-columns: 1fr;
  }
}
</style>
