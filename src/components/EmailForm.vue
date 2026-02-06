<script setup lang="ts">
import { ref, computed } from 'vue';
import { Mail, Sparkles, Languages, Send, User, AtSign, Type, Loader2 } from 'lucide-vue-next';
import { polishMessage, translateMessage, sendEmailViaProxy } from '../services/aiService';

const form = ref({
  clientName: '',
  clientEmail: '',
  subject: '',
  message: '',
});

const isEnglish = ref(false); // false = ES -> EN, true = EN -> ES
const translatedMessage = ref('');
const isPolishing = ref(false);
const isTranslating = ref(false);
const isSending = ref(false);

const translationDirection = computed(() => 
  isEnglish.value ? 'English ➔ Español' : 'Español ➔ English'
);

const handleTranslate = async () => {
  if (!form.value.message) return;
  isTranslating.value = true;
  try {
    translatedMessage.value = await translateMessage(form.value.message, !isEnglish.value);
  } catch (error) {
    alert('Translation failed. Check API key.');
  } finally {
    isTranslating.value = false;
  }
};

const handlePolish = async () => {
  if (!form.value.message) return;
  isPolishing.value = true;
  try {
    const refined = await polishMessage(form.value.message, isEnglish.value);
    form.value.message = refined;
    // Auto-translate after polish
    handleTranslate();
  } catch (error) {
    alert('AI Polish failed. Check API key.');
  } finally {
    isPolishing.value = false;
  }
};

const sendEmail = async () => {
  if (!translatedMessage.value) {
    await handleTranslate();
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
            <p style="font-style: italic; color: #a0a0c0;">[Source Language: ${isEnglish.value ? 'English' : 'Español'}]</p>
            <p>${form.value.message}</p>
          </div>
          <div style="margin-top: 20px; border-left: 3px solid #00f7ff; padding-left: 15px;">
            <p style="font-weight: bold; color: #00f7ff;">[Translated Translation]</p>
            <p>${translatedMessage.value}</p>
          </div>
          <footer style="margin-top: 30px; font-size: 0.8rem; color: #a0a0c0; border-top: 1px solid rgba(0,247,255,0.1); padding-top: 15px;">
            Navy Tech Design - Secure Transmission Initialized
          </footer>
        </div>
      `
    };

    const response = await sendEmailViaProxy(templateParams);
    alert(response.message || 'Message sent successfully via Security Bridge!');
    form.value = { clientName: '', clientEmail: '', subject: '', message: '' };
    translatedMessage.value = '';
  } catch (error) {
    console.error('Email send failed:', error);
    alert('Failed to send email via proxy. Check config.php and server logs.');
  } finally {
    isSending.value = false;
  }
};
</script>

<template>
  <div class="tech-card email-form-container">
    <div class="form-header">
      <h2 class="glow-text">Secure Mailer</h2>
      <div class="lang-switcher">
        <span :class="{ active: !isEnglish }">ES</span>
        <button class="switch-btn" @click="isEnglish = !isEnglish">
          <Languages :size="20" :class="{ 'flip-icon': isEnglish }" />
        </button>
        <span :class="{ active: isEnglish }">EN</span>
      </div>
    </div>

    <form @submit.prevent="sendEmail" class="main-form">
      <div class="input-row">
        <div class="input-group">
          <label><User :size="14" /> Client Name</label>
          <input v-model="form.clientName" type="text" placeholder="John Wick" required />
        </div>
        <div class="input-group">
          <label><AtSign :size="14" /> Client Email</label>
          <input v-model="form.clientEmail" type="email" placeholder="john@continental.com" required />
        </div>
      </div>

      <div class="input-group">
        <label><Type :size="14" /> Subject</label>
        <input v-model="form.subject" type="text" placeholder="Project Alpha - Phase 1" required />
      </div>

      <div class="message-section">
        <div class="section-header">
          <label><Mail :size="14" /> Source Message ({{ isEnglish ? 'English' : 'Español' }})</label>
          <button type="button" class="action-link" @click="handlePolish" :disabled="isPolishing">
            <Loader2 v-if="isPolishing" :size="14" class="spin" />
            <Sparkles v-else :size="14" /> 
            {{ isPolishing ? 'Refining...' : 'AI Polish' }}
          </button>
        </div>
        <textarea 
          v-model="form.message" 
          rows="5" 
          placeholder="Escribe tu mensaje aquí..." 
          @blur="handleTranslate"
        ></textarea>
      </div>

      <div class="translation-section" v-if="form.message">
        <div class="section-header">
          <label><Languages :size="14" /> {{ translationDirection }} Result</label>
          <button type="button" class="action-link" @click="handleTranslate" :disabled="isTranslating">
            <Loader2 v-if="isTranslating" :size="14" class="spin" />
            {{ isTranslating ? 'Translating...' : 'Force Refresh' }}
          </button>
        </div>
        <div class="preview-box">
          {{ translatedMessage || (isTranslating ? 'Generating...' : 'Awaiting input...') }}
        </div>
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

.lang-switcher {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: bold;
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.lang-switcher .active {
  color: var(--accent);
  text-shadow: 0 0 8px var(--accent-glow);
}

.switch-btn {
  background: var(--bg-main);
  border: 1px solid var(--accent);
  color: var(--accent);
  border-radius: 20px;
  padding: 0.2rem 0.6rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.switch-btn:hover {
  box-shadow: 0 0 10px var(--accent-glow);
}

.flip-icon {
  transform: rotate(180deg);
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

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.action-link {
  background: none;
  border: none;
  color: var(--accent);
  font-size: 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  opacity: 0.7;
  transition: all 0.3s;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.action-link:hover:not(:disabled) {
  opacity: 1;
  text-shadow: 0 0 8px var(--accent-glow);
}

.action-link:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.preview-box {
  background: rgba(0, 247, 255, 0.02);
  border: 1px dashed var(--border-color);
  border-radius: 6px;
  padding: 1rem;
  font-style: italic;
  font-size: 0.9rem;
  color: var(--text-secondary);
  min-height: 80px;
  white-space: pre-wrap;
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
