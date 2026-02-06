<script setup lang="ts">
import EmailForm from './components/EmailForm.vue'
</script>

<template>
  <div class="app-container">
    <header class="main-header">
      <h1 class="glow-text">Secure <span class="accent-text">Mailer</span></h1>
    </header>

    <main>
      <EmailForm />
    </main>

    <footer class="main-footer">
      <p>&copy; 2025 Felipe Miramontes. Navy Tech Design.</p>
    </footer>
  </div>
</template>

<style scoped>
.app-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 40px 20px 20px;
  max-width: 100%;
}

main {
  margin: 20px 0;
}

.main-header {
  margin-bottom: 0;
}

h1 {
  font-size: 2.5rem;
  margin: 0;
}

.accent-text {
  color: var(--accent);
}

.subtitle {
  color: var(--text-secondary);
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.main-footer {
  font-size: 0.8rem;
  color: var(--text-secondary);
  opacity: 0.6;
}
</style>
