<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Mail, Send, User, AtSign, Type, Loader2 } from 'lucide-vue-next';
import { sendEmailViaProxy } from '../services/aiService';

const form = ref({
  clientName: 'B. Eng. Felipe de Jesús Miramontes Romero',
  clientEmail: 'info@felipemiramontesr.net',
  subject: '',
  message: '',
});

const isSending = ref(false);

const sendEmail = async () => {
  if (!form.value.message || !form.value.subject) {
    return;
  }

  isSending.value = true;
  
  try {
    const templateParams = {
      to_name: form.value.clientName,
      to_email: form.value.clientEmail,
      subject: form.value.subject,
      body: `
        <div style="background-color: #080b1a; color: #ffffff; padding: 20px; font-family: 'Inter', sans-serif;">
          <h2 style="color: #00f7ff; border-bottom: 1px solid rgba(0,247,255,0.2); padding-bottom: 10px;">Security Mailer - Message</h2>
          <p><strong>From:</strong> ${form.value.clientName}</p>
          <p><strong>Subject:</strong> ${form.value.subject}</p>
          <hr style="border: 0; border-top: 1px solid rgba(0,247,255,0.1); margin: 20px 0;">
          <div style="background: rgba(17, 22, 51, 0.8); padding: 15px; border-radius: 6px;">
            <p>${form.value.message}</p>
          </div>
          <footer style="margin-top: 30px; font-size: 0.8rem; color: #a0a0c0; border-top: 1px solid rgba(0,247,255,0.1); padding-top: 15px;">
            Navy Tech Design - Secure Transmission Initialized
          </footer>
        </div>
      `
    };

    const response = await sendEmailViaProxy(templateParams);
    alert(response.message || 'Message sent successfully!');
    // Keep name and email, just clear subject and message
    form.value.subject = '';
    form.value.message = '';
  } catch (error) {
    console.error('Email send failed:', error);
    // No more intrusive alerts, just log it. 
    // If the user wants an error feedback, we can add it to the UI later.
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

      <button type="submit" class="cyan-btn send-btn" :disabled="isSending">
        <Loader2 v-if="isSending" :size="20" class="spin" />
        <Send v-else :size="20" /> 
        {{ isSending ? 'DECRYPTING & SENDING...' : 'INITIALIZE SEND SEQUENCE' }}
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
