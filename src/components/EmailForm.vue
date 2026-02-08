<script setup lang="ts">
import { ref } from 'vue';
import { Mail, Send, ShieldCheck, Satellite, Type, Loader2, CheckCircle2, AlertCircle, Eye, EyeOff } from 'lucide-vue-next';
import { sendEmailViaProxy } from '../services/aiService';

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
const security = ref({
  password: '',
  authCode: '',
  showPinField: false
});

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
        <div style="display:none; max-height:0px; max-width:0px; opacity:0; overflow:hidden; font-size:1px; line-height:1px; color:#080b1a;">
          ${form.value.subject} &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        </div>
        <div style="background: radial-gradient(circle at 50% 0%, #1a224d 0%, #080b2a 100%); color: #ffffff; padding: 0; font-family: 'Inter', Arial, sans-serif; max-width: 1000px; width: 95%; margin: 20px auto; border: 1px solid rgba(0, 247, 255, 0.25); box-sizing: border-box; border-radius: 12px; overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.7);">
          <!-- Glowing Top Bar -->
          <div style="height: 4px; background: linear-gradient(90deg, transparent, #00f7ff, transparent); box-shadow: 0 0 15px #00f7ff;"></div>
          
          <div style="padding: 25px 40px;">
            <!-- HUD System Header -->
            <div style="border-bottom: 1px solid rgba(0, 247, 255, 0.3); padding-bottom: 12px; margin-bottom: 20px; display: table; width: 100%;">
              <div style="display: table-cell; vertical-align: middle;">
                <span style="color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: 3px; text-shadow: 0 0 10px rgba(0, 247, 255, 0.5);">felipemiramontesr&zwnj;.net</span>
              </div>
              <div style="display: table-cell; text-align: right; vertical-align: middle; color: #7e8ec2; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px;">
                LOG_STREAM // ${new Date().toISOString().split('T')[0]}
              </div>
            </div>

            <!-- Main Interface Card (Upper Glass) -->
            <div style="background: rgba(255, 255, 255, 0.07); padding: 20px 30px; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.1); position: relative; box-shadow: inset 0 0 50px rgba(0, 247, 255, 0.05); margin-bottom: 15px;">
              
              <!-- Sender Info (Unified Style) -->
              <div style="margin-bottom: 12px;">
                <div style="margin-bottom: 4px;">
                  <span style="color: #7e8ec2; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px; text-transform: uppercase;">👤 ORIGIN_POINT</span>
                </div>
                <div style="background: rgba(0, 247, 255, 0.08); border-left: 4px solid #00f7ff; padding: 8px 15px; border-radius: 0 8px 8px 0; box-shadow: 0 0 20px rgba(0, 247, 255, 0.05);">
                  <p style="margin: 0; color: #00f7ff; font-size: 15px; font-weight: 600; text-shadow: 0 0 15px rgba(0, 247, 255, 0.4); text-transform: uppercase; letter-spacing: 1px;">
                    B. Eng. Felipe de Jesús Miramontes Romero
                  </p>
                </div>
              </div>
              
              <!-- Subject Info (Unified Style) -->
              <div style="margin-bottom: 12px;">
                <div style="margin-bottom: 4px;">
                  <span style="color: #7e8ec2; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px; text-transform: uppercase;">🛰️ TRANSMISSION_SUBJECT</span>
                </div>
                <div style="background: rgba(0, 247, 255, 0.1); border-left: 4px solid #00f7ff; padding: 10px 15px; border-radius: 0 8px 8px 0; box-shadow: 0 0 25px rgba(0, 247, 255, 0.1);">
                  <p style="margin: 0; color: #00f7ff; font-size: 18px; font-weight: 700; text-shadow: 0 0 20px rgba(0, 247, 255, 0.5); letter-spacing: 1px;">
                    ${form.value.subject}
                  </p>
                </div>
              </div>
              
              <!-- Content Section -->
              <div style="border-top: 1px solid rgba(0, 247, 255, 0.2); padding-top: 20px; margin-top: 10px;">
                <div style="margin-bottom: 8px;">
                  <span style="color: #ffffff; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">📥 SIGNAL_DATA</span>
                </div>
                <div style="color: #ffffff; line-height: 1.6; font-size: 15px; padding: 25px; background: rgba(0, 0, 0, 0.5); border-radius: 12px; border: 1px solid rgba(0, 247, 255, 0.3); box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.8), 0 10px 30px rgba(0,0,0,0.5); position: relative;">
                  <div style="color: rgba(0, 247, 255, 0.4); font-family: 'Orbitron', sans-serif; font-size: 8px; margin-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 8px; letter-spacing: 4px;">// START</div>
                  
                  <div style="white-space: pre-wrap; word-break: break-word;">${form.value.message}</div>
                  
                  <div style="color: rgba(0, 247, 255, 0.4); font-family: 'Orbitron', sans-serif; font-size: 8px; margin-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 10px; text-align: right; letter-spacing: 4px;">// END</div>
                </div>
              </div>
            </div>
            
            <!-- Technical Footer -->
            <div style="margin-top: 15px; display: table; width: 100%; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 15px;">
              <div style="display: table-cell; vertical-align: middle;">
                <p style="color: #00f7ff; font-size: 10px; margin: 0; font-family: 'Orbitron', sans-serif; letter-spacing: 2px; font-weight: 600;">🛡️ INTEGRITY: OPTIMAL</p>
                <p style="color: #7e8ec2; font-size: 9px; margin-top: 4px; font-family: 'Orbitron', sans-serif; opacity: 0.8;">PROTO: AES_256 // TLS_1.3</p>
              </div>
              <div style="display: table-cell; text-align: right; vertical-align: middle;">
                <div style="width: 10px; height: 10px; background: #00f7ff; border-radius: 50%; display: inline-block; box-shadow: 0 0 20px #00f7ff;"></div>
              </div>
            </div>
          </div>
        </div>
      `
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
          <input v-model="form.clientEmail" type="email" placeholder="felipemiramontesr@gmail.com" required autocomplete="off" />
        </div>
      </div>

      <div class="input-group">
        <label><Type :size="14" /> SUBJECT</label>
        <input v-model="form.subject" type="text" placeholder="Insert a massage subject" required />
      </div>

      <div class="message-section">
        <label><Mail :size="14" /> Message Content</label>
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
  margin-bottom: 10px;
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

@media (max-width: 600px) {
  .input-row {
    grid-template-columns: 1fr;
  }
}
</style>
