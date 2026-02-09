/**
 * Mailer Project Types
 * Centralized interfaces for data structures and service responses.
 */

/**
 * Form data for the email composition
 */
export interface EmailFormData {
  clientName: string;
  clientEmail: string;
  subject: string;
  message: string;
}

/**
 * Metadata for security and auth states
 */
export interface SecurityState {
  password: string;
  authCode: string;
  showPinField: boolean;
  blackOpsMode?: boolean; // Phase 6: ZK-Delivery
  burnTimer?: number; // Phase 6.1: Chronos Protocol
}

/**
 * Possible status states for the email sending process
 */
export type SendStatus = 'idle' | 'sending' | 'success' | 'error' | 'awaiting_2fa';

/**
 * Structured response from the PHP Proxy
 */
export interface ProxyResponse {
  status?: SendStatus;
  success?: boolean;
  message?: string;
  error?: string;
  details?: string;
  result?: string;
}

/**
 * Parameters for the email service
 */
export interface EmailPayload {
  to_name: string;
  to_email: string;
  subject: string;
  body: string;
  burn_timer?: number; // Seconds until autodestroy
}
