<script setup lang="ts">
import { nextTick, ref, watch } from 'vue';
/**
 * EmailForm Component
 * Main interface for composing and sending encrypted signal transmissions (emails).
 * Utilizes a HUD v4 design aesthetic and modular composable logic.
 */
import {
  AlertCircle,
  CheckCircle2,
  Eye,
  EyeOff,
  Layout,
  Loader2,
  Mail,
  Satellite,
  Send,
  ShieldAlert,
  ShieldCheck,
  Type,
  X,
} from 'lucide-vue-next';
import { useEmailForm } from '../composables/useEmailForm';

const {
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
  toasts,
  removeToast,
  logs,
} = useEmailForm();

const terminalBody = ref<HTMLElement | null>(null);

watch(
  logs,
  () => {
    nextTick(() => {
      if (terminalBody.value) {
        terminalBody.value.scrollTop = terminalBody.value.scrollHeight;
      }
    });
  },
  { deep: true }
);
</script>

<template>
  <div class="tech-card email-form-container" :class="{ 'with-preview': showPreview }">
    <!-- Toast System -->
    <div class="toast-container">
      <TransitionGroup name="toast">
        <div v-for="toast in toasts" :key="toast.id" class="hud-toast" :class="toast.type">
          <div class="toast-content">{{ toast.message }}</div>
          <button @click="removeToast(toast.id)" class="toast-close">
            <X :size="14" />
          </button>
        </div>
      </TransitionGroup>
    </div>
    <div class="form-header">
      <h2 class="glow-text">felipemiramontesr.net</h2>

      <div class="header-status-area">
        <div class="status-indicator"><span class="dot"></span> SECURE_CONNECTION</div>

        <button
          type="button"
          class="preview-toggle"
          @click="showPreview = !showPreview"
          :class="{ active: showPreview }"
        >
          <Layout v-if="!showPreview" :size="14" />
          <EyeOff v-else :size="14" />
          LP
        </button>
      </div>
    </div>

    <div class="form-layout">
      <form @submit.prevent="handleSubmit" class="main-form" autocomplete="off">
        <div class="input-row">
          <div class="input-group icon-inside">
            <ShieldCheck :size="16" class="inner-icon" />
            <input v-model="form.clientName" type="text" readonly autocomplete="off" />
          </div>
          <div class="input-group icon-inside">
            <Satellite :size="16" class="inner-icon" />
            <input
              v-model="form.clientEmail"
              type="email"
              placeholder="felipemiramontesr@gmail.com"
              required
              autocomplete="off"
            />
          </div>
        </div>

        <div class="input-group">
          <label><Type :size="14" /> SUBJECT</label>
          <input
            v-model="form.subject"
            type="text"
            placeholder="Insert a massage subject"
            required
          />
        </div>

        <!-- Black-Ops Toggle (Phase 6) -->
        <div class="black-ops-field">
          <div class="black-ops-card" :class="{ morphed: security.blackOpsMode }">
            <div
              class="black-ops-main-info"
              @click="security.blackOpsMode = !security.blackOpsMode"
            >
              <div class="black-ops-info">
                <ShieldAlert
                  class="field-icon"
                  :size="20"
                  :class="{ active: security.blackOpsMode }"
                />
                <div class="black-ops-text">
                  <span class="black-ops-label">BLACK_OPS_ENCRYPTION (ZCSD)</span>
                  <span class="black-ops-hint">Zero-Knowledge Secure Delivery Active</span>
                </div>
              </div>
              <div class="black-ops-switch" :class="{ active: security.blackOpsMode }">
                <div class="switch-handle"></div>
              </div>
            </div>

            <!-- Chronos Timer Expansion -->
            <div v-if="security.blackOpsMode" class="chronos-timer-area animate-slide-in">
              <div class="timer-divider"></div>
              <div class="timer-input-wrapper">
                <label class="timer-label">AUTODESTRUCT_TMR (SEC)</label>
                <input
                  v-model.number="security.burnTimer"
                  type="number"
                  min="5"
                  max="3600"
                  class="timer-input"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="message-section">
          <label><Mail :size="14" /> MESSAGE CONTENT</label>
          <textarea
            v-model="form.message"
            rows="4"
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
          :disabled="!isFormValid"
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
        <div class="viewfinder-corner top-left"></div>
        <div class="viewfinder-corner top-right"></div>
        <div class="viewfinder-corner bottom-left"></div>
        <div class="viewfinder-corner bottom-right"></div>

        <div class="preview-header">
          <div class="header-led"></div>
          <div class="preview-badge">HUD_LIVE_FEED // V5.2_MIRROR</div>
        </div>

        <div class="preview-scroll-area">
          <div class="scanline"></div>
          <!-- Skeleton Shimmer Loader -->
          <div v-if="isSending" class="skeleton-overlay">
            <div class="skeleton-item skeleton-header"></div>
            <div class="skeleton-item skeleton-line"></div>
            <div class="skeleton-item skeleton-line mid"></div>
            <div class="skeleton-item skeleton-box"></div>
          </div>
          <div class="email-canvas" v-html="emailTemplateHTML"></div>
        </div>

        <!-- Integrated Technical Terminal -->
        <div class="hud-terminal">
          <div class="terminal-header">
            <span class="terminal-title">TERMINAL_LOG // EVENT_STREAM</span>
            <span class="terminal-status">LIVE_RELAY</span>
          </div>
          <div class="terminal-body" ref="terminalBody">
            <div v-for="log in logs" :key="log.id" class="log-entry">
              <span class="log-time">[{{ log.timestamp }}]</span>
              <span class="log-level" :class="log.level.toLowerCase()">{{ log.level }}</span>
              <span class="log-msg">{{ log.message }}</span>
            </div>
          </div>
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

/* Preview Styles */
.live-preview-container {
  flex: 1.8;
  background: #030a16;
  border: 1px solid rgba(0, 247, 255, 0.3);
  border-radius: 4px;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow:
    0 0 40px rgba(0, 0, 0, 0.8),
    inset 0 0 100px rgba(0, 247, 255, 0.05);
  align-self: stretch;
  background-image:
    radial-gradient(circle at 50% 50%, rgba(0, 247, 255, 0.08) 0%, transparent 80%),
    linear-gradient(rgba(0, 247, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 247, 255, 0.03) 1px, transparent 1px);
  background-size:
    100% 100%,
    25px 25px,
    25px 25px;
}

.viewfinder-corner {
  position: absolute;
  width: 15px;
  height: 15px;
  border: 2px solid var(--accent);
  z-index: 5;
  opacity: 0.5;
}
.top-left {
  top: 10px;
  left: 10px;
  border-right: none;
  border-bottom: none;
}
.top-right {
  top: 10px;
  right: 10px;
  border-left: none;
  border-bottom: none;
}
.bottom-left {
  bottom: 10px;
  left: 10px;
  border-right: none;
  border-top: none;
}
.bottom-right {
  bottom: 10px;
  right: 10px;
  border-left: none;
  border-top: none;
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
  padding: 20px;
  display: flex;
  justify-content: center;
  position: relative;
}

.scanline {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: rgba(0, 247, 255, 0.15);
  z-index: 10;
  pointer-events: none;
  animation: scanline 6s linear infinite;
  box-shadow: 0 0 10px rgba(0, 247, 255, 0.2);
}

@keyframes scanline {
  from {
    top: -5%;
  }
  to {
    top: 105%;
  }
}

.email-canvas {
  width: 100%;
  max-width: 1000px;
  background: transparent;
  zoom: 0.75;
  flex-shrink: 0;
  transform-origin: top center;
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

input,
textarea {
  width: 100%;
  background: rgba(8, 11, 26, 0.5);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  padding: 0.8rem;
  color: white;
  transition: all 0.3s ease;
  font-family: 'Inter', sans-serif;
}

input:focus,
textarea:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 15px rgba(0, 247, 255, 0.25);
  background: rgba(17, 22, 51, 0.9);
  transform: translateY(-1px);
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

/* Skeleton & Shimmer */
.skeleton-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(8, 11, 26, 0.8);
  display: flex;
  flex-direction: column;
  padding: 40px;
  gap: 20px;
  z-index: 10;
}

.skeleton-item {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.05) 25%,
    rgba(255, 255, 255, 0.1) 50%,
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
}

.skeleton-header {
  height: 40px;
  width: 60%;
}
.skeleton-line {
  height: 20px;
  width: 100%;
}
.skeleton-line.mid {
  width: 80%;
}
.skeleton-box {
  height: 150px;
  width: 100%;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Toast System */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.hud-toast {
  background: rgba(8, 11, 26, 0.9);
  border-left: 4px solid var(--accent);
  border-right: 1px solid rgba(0, 247, 255, 0.1);
  border-top: 1px solid rgba(0, 247, 255, 0.1);
  border-bottom: 1px solid rgba(0, 247, 255, 0.1);
  padding: 12px 20px;
  color: white;
  display: flex;
  align-items: center;
  gap: 15px;
  min-width: 250px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  font-family: 'Inter', sans-serif;
  font-size: 0.9rem;
}

.hud-toast.success {
  border-left-color: #00ff88;
}
.hud-toast.error {
  border-left-color: #ff4444;
}
.hud-toast.warning {
  border-left-color: #ffcc00;
}

.toast-close {
  margin-left: auto;
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 4px;
  display: flex;
  opacity: 0.6;
}

.toast-close:hover {
  opacity: 1;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s ease;
}
.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* HUD Terminal */
.hud-terminal {
  background: rgba(0, 0, 0, 0.8);
  border-top: 1px solid rgba(0, 247, 255, 0.2);
  height: 120px;
  display: flex;
  flex-direction: column;
}

.terminal-header {
  padding: 4px 12px;
  background: rgba(0, 247, 255, 0.05);
  border-bottom: 1px solid rgba(0, 247, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.terminal-title {
  font-family: 'Orbitron', sans-serif;
  font-size: 0.6rem;
  letter-spacing: 1.5px;
  color: var(--text-secondary);
}

.terminal-status {
  font-family: 'Orbitron', sans-serif;
  font-size: 0.55rem;
  color: var(--accent);
  opacity: 0.8;
}

.terminal-body {
  flex: 1;
  overflow-y: auto;
  padding: 8px 12px;
  font-family: 'JetBrains Mono', 'Courier New', monospace;
  font-size: 0.65rem;
  display: flex;
  flex-direction: column;
}

.terminal-body::-webkit-scrollbar {
  width: 3px;
}
.terminal-body::-webkit-scrollbar-thumb {
  background: var(--accent);
  opacity: 0.3;
}

.log-entry {
  display: flex;
  gap: 8px;
  margin-bottom: 2px;
  white-space: nowrap;
}

.log-time {
  color: #5b6ea3;
}
.log-level {
  font-weight: bold;
  min-width: 50px;
}
.log-level.system {
  color: #00f7ff;
}
.log-level.network {
  color: #bb86fc;
}
.log-level.auth {
  color: #ffcc00;
}
.log-level.ai {
  color: #00ff88;
}
.log-msg {
  color: #cbd5e1;
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

.black-ops-field {
  margin: 20px 0;
}

.black-ops-card {
  background: rgba(255, 60, 0, 0.05);
  border: 1px solid rgba(255, 60, 0, 0.1);
  padding: 15px;
  border-radius: 8px;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  gap: 0;
}

.black-ops-card.morphed {
  background: rgba(255, 60, 0, 0.08);
  border-color: rgba(255, 60, 0, 0.4);
  box-shadow: 0 0 20px rgba(255, 60, 0, 0.1);
}

.black-ops-main-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1;
  cursor: pointer;
  transition: all 0.5s ease;
}

.morphed .black-ops-main-info {
  flex: 0 0 60%;
  border-right: 1px solid rgba(255, 60, 0, 0.2);
  padding-right: 20px;
}

.black-ops-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.chronos-timer-area {
  flex: 1;
  display: flex;
  align-items: center;
  padding-left: 20px;
  gap: 15px;
}

.timer-input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.timer-label {
  font-family: 'Orbitron', sans-serif;
  font-size: 0.6rem;
  color: #ff3c00;
  opacity: 0.8;
  letter-spacing: 1px;
}

.timer-input {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 60, 0, 0.3);
  border-radius: 4px;
  padding: 4px 8px;
  color: #ff3c00;
  font-family: 'Fira Code', monospace;
  font-size: 0.9rem;
  width: 80px;
  text-align: center;
}

.animate-slide-in {
  animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.black-ops-text {
  display: flex;
  flex-direction: column;
}

.black-ops-label {
  font-family: 'Orbitron', sans-serif;
  font-size: 0.85rem;
  font-weight: 700;
  color: #ff3c00;
  letter-spacing: 1px;
}

.black-ops-hint {
  font-size: 0.7rem;
  color: rgba(255, 60, 0, 0.6);
  text-transform: uppercase;
}

.field-icon.active {
  filter: drop-shadow(0 0 8px #ff3c00);
}

.black-ops-switch {
  width: 44px;
  height: 22px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  position: relative;
  transition: all 0.3s;
}

.black-ops-switch.active {
  background: #ff3c00;
  box-shadow: 0 0 15px rgba(255, 60, 0, 0.4);
}

.switch-handle {
  width: 16px;
  height: 16px;
  background: #fff;
  border-radius: 50%;
  position: absolute;
  top: 3px;
  left: 3px;
  transition: all 0.3s;
}

.black-ops-switch.active .switch-handle {
  left: 25px;
}

.black-ops-field {
  margin: 18px 0;
}

.black-ops-card {
  background: rgba(255, 60, 0, 0.05);
  border: 1px solid rgba(255, 60, 0, 0.15);
  padding: 14px;
  border-radius: 4px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
  position: relative;
  overflow: hidden;
}

.black-ops-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 60, 0, 0.1), transparent);
  transition: 0.5s;
}

.black-ops-card:hover::after {
  left: 100%;
}

.black-ops-card:hover {
  background: rgba(255, 60, 0, 0.08);
  border-color: rgba(255, 60, 0, 0.4);
  box-shadow: 0 0 15px rgba(255, 60, 0, 0.1);
}

.black-ops-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.black-ops-text {
  display: flex;
  flex-direction: column;
}

.black-ops-label {
  font-family: 'Orbitron', sans-serif;
  font-size: 0.75rem;
  font-weight: 800;
  color: #ff3c00;
  letter-spacing: 2px;
}

.black-ops-hint {
  font-size: 0.65rem;
  color: rgba(255, 255, 255, 0.4);
  text-transform: uppercase;
  margin-top: 2px;
}

.field-icon {
  color: rgba(255, 60, 0, 0.3);
  transition: all 0.3s;
}

.field-icon.active {
  color: #ff3c00;
  filter: drop-shadow(0 0 8px #ff3c00);
}

.black-ops-switch {
  width: 38px;
  height: 20px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  position: relative;
  transition: all 0.4s;
}

.black-ops-switch.active {
  background: rgba(255, 60, 0, 0.2);
  border-color: #ff3c00;
}

.switch-handle {
  width: 14px;
  height: 14px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 3px;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.black-ops-switch.active .switch-handle {
  left: 19px;
  background: #ff3c00;
  box-shadow: 0 0 10px #ff3c00;
}
</style>
