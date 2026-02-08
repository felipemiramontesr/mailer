# Navy Tech Mailer - Professional Node V3

A production-grade web interface for secure, AI-powered email transmissions. Built with a high-fidelity HUD v4 aesthetic and protected by a dual-layer security transponder.

## 🚀 Tech Stack

- **Frontend**: Vue 3 (Composition API) + Vite + TypeScript
- **Logic Layers**: Vue Composables for state management
- **Aesthetics**: Vanilla CSS with HUD v4 design tokens (Glassmorphism & Micro-animations)
- **Backend**: Secure PHP Proxy (SMTP via PHPMailer + Gemini AI Integration)
- **Quality**: Vitest + GitHub Actions (CI/CD)

## 🛠️ Installation & Setup

1.  **Clone & Install**:

    ```bash
    git clone [repository-url]
    npm install
    ```

2.  **Configuration**:
    - Copy `.env.example` to `.env`.
    - Configure your SMTP and Gemini API secrets.
    - Run `node scripts/inject-secrets.cjs` to synchronize with the backend.

3.  **Development**:

    ```bash
    npm run dev
    ```

4.  **Testing**:
    ```bash
    npm test
    ```

## 🛡️ Security Protocol

- **Dual-Layer Auth**: Requires Master Access Key + 2FA PIN (dispatched to master email).
- **Environment Isolation**: Secrets are injected server-side and never exposed to the client bundle.
- **Data Integrity**: AES-256 and TLS 1.3 encryption protocols simulated in headers.

---

_Developed with precision for mission-critical communication._
