import { describe, expect, it } from 'vitest';
import { generateEmailTemplate } from '../utils/emailTemplate';

describe('Email Template Utility', () => {
  const mockForm = {
    clientName: 'Felipe Romero',
    clientEmail: 'felipe@example.com',
    subject: 'Test Subject',
    message: 'Hello World',
  };

  it('should include the professional commander name in the template', () => {
    const html = generateEmailTemplate(mockForm);
    expect(html).toContain('B. Eng. Felipe de Jesús Miramontes Romero');
  });

  it('should include the raw subject provided by the user', () => {
    const html = generateEmailTemplate(mockForm);
    expect(html).toContain('Test Subject');
  });

  it('should show placeholder when subject is missing', () => {
    const html = generateEmailTemplate({ ...mockForm, subject: '' });
    expect(html).toContain('[ Subject missing ]');
  });

  it('should include the message content', () => {
    const html = generateEmailTemplate(mockForm);
    expect(html).toContain('Hello World');
  });

  it('should have a fluid width of 100%', () => {
    const html = generateEmailTemplate(mockForm);
    expect(html).toContain('width: 100%');
  });

  it('should contain the current date in the DATA_STREAM', () => {
    const html = generateEmailTemplate(mockForm);
    const currentDate = new Date().toISOString().split('T')[0];
    expect(html).toContain(`DATA_STREAM // ${currentDate}`);
  });
});
