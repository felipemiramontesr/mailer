import type { EmailFormData } from '../types';

/**
 * Generates the professional HUD-themed HTML email template.
 * @param form - The data containing sender name, subject, and message.
 * @returns A complete HTML string formatted for email clients.
 */
export const generateEmailTemplate = (form: EmailFormData): string => {
  const currentDate = new Date().toISOString().split('T')[0];

  return `
  <div style="display:none; max-height:0px; max-width:0px; opacity:0; overflow:hidden; font-size:1px; line-height:1px; color:#080b2a;">
    ${form.subject || 'Subject Preview'} &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
  </div>
  <div style="background: radial-gradient(circle at 50% 0%, #1a224d 0%, #080b2a 100%); color: #ffffff; padding: 0; font-family: 'Inter', Arial, sans-serif; width: 1000px; margin: 20px auto; border: 1px solid rgba(0, 247, 255, 0.25); box-sizing: border-box; border-radius: 12px; overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.7);">
    <!-- Glowing Top Bar -->
    <div style="height: 4px; background: linear-gradient(90deg, transparent, #00f7ff, transparent); box-shadow: 0 0 15px #00f7ff;"></div>
    
    <div style="padding: 15px 30px;">
      <!-- HUD System Header -->
      <div style="border-bottom: 1px solid rgba(0, 247, 255, 0.3); padding-bottom: 10px; margin-bottom: 15px; display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: middle; width: 65%;">
          <a href="https://felipemiramontesr.net" style="color: #00f7ff; font-family: 'Inter', Arial, sans-serif; font-size: 14px; font-weight: 500; text-decoration: none; letter-spacing: 2px; text-shadow: 0 0 10px rgba(0, 247, 255, 0.5);">felipemiramontesr&zwnj;.net</a>
        </div>
        <div style="display: table-cell; text-align: right; vertical-align: middle; width: 35%; color: #7e8ec2; font-family: Arial, sans-serif; font-size: 9px; letter-spacing: 1px; white-space: nowrap;">
          LOG_STREAM // ${currentDate}
        </div>
      </div>
 
      <!-- Main Interface Card (Upper Glass) -->
      <div style="background: rgba(255, 255, 255, 0.07); padding: 15px 25px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); position: relative; box-shadow: inset 0 0 50px rgba(0, 247, 255, 0.05); margin-bottom: 12px;">
        
        <!-- Sender Info (Unified Style) -->
        <div style="margin-bottom: 10px;">
          <div style="margin-bottom: 4px;">
            <span style="color: #7e8ec2; font-family: Arial, sans-serif; font-size: 8px; letter-spacing: 2px; text-transform: uppercase;">👤 ORIGIN_POINT</span>
          </div>
          <div style="background: rgba(0, 247, 255, 0.08); border-left: 3px solid #00f7ff; padding: 8px 15px; border-radius: 0 6px 6px 0;">
            <p style="margin: 0; color: #00f7ff; font-size: 14px; font-weight: 600; text-shadow: 0 0 10px rgba(0, 247, 255, 0.4); text-transform: uppercase; letter-spacing: 1px; line-height: 20px;">
              ${form.clientName}
            </p>
          </div>
        </div>
        
        <!-- Subject Info (Unified Style) -->
        <div style="margin-bottom: 10px;">
          <div style="margin-bottom: 4px;">
            <span style="color: #7e8ec2; font-family: Arial, sans-serif; font-size: 8px; letter-spacing: 2px; text-transform: uppercase;">🛰️ TRANSMISSION_SUBJECT</span>
          </div>
          <div style="background: rgba(0, 247, 255, 0.1); border-left: 3px solid #00f7ff; padding: 8px 15px; border-radius: 0 6px 6px 0;">
            <p style="margin: 0; color: #00f7ff; font-size: 16px; font-weight: 500; font-family: 'Inter', sans-serif; letter-spacing: 0.5px; text-transform: none; line-height: 20px;">
              ${form.subject || '[ Subject missing ]'}
            </p>
          </div>
        </div>
        
        <!-- Content Section -->
        <div style="border-top: 1px solid rgba(0, 247, 255, 0.2); padding-top: 15px; margin-top: 5px;">
          <div style="margin-bottom: 6px;">
            <span style="color: #ffffff; font-family: Arial, sans-serif; font-size: 9px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">📥 SIGNAL_DATA</span>
          </div>
          <div style="color: #ffffff; line-height: 1.5; font-size: 14px; padding: 15px 20px; background: rgba(0, 0, 0, 0.5); border-radius: 8px; border: 1px solid rgba(0, 247, 255, 0.3); box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.8), 0 5px 15px rgba(0,0,0,0.5); position: relative;">
            <div style="color: rgba(0, 247, 255, 0.4); font-family: Arial, sans-serif; font-size: 8px; margin-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 6px; letter-spacing: 2px;">
              // [ START_TRANSMISSION ]
            </div>
            
            <div style="white-space: pre-wrap; word-break: break-word; min-height: 80px;">${form.message || 'Waiting for signal input...'}</div>
 
            <div style="color: rgba(0, 247, 255, 0.4); font-family: Arial, sans-serif; font-size: 8px; margin-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 8px; letter-spacing: 2px;">
              // [ END_SIGNAL ]
            </div>
          </div>
        </div>
      </div>
      
      <!-- Technical Footer -->
      <div style="margin-top: 10px; display: table; width: 100%; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 10px;">
        <div style="display: table-cell; vertical-align: middle; text-align: left;">
          <span style="color: #00f7ff; font-size: 9px; font-family: Arial, sans-serif; letter-spacing: 1.5px; font-weight: 600; text-transform: uppercase;">🛡️ INTEGRITY: OK</span>
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
          <span style="color: #7e8ec2; font-size: 8px; font-family: Arial, sans-serif; opacity: 0.8; letter-spacing: 1px; text-transform: uppercase;">PROTO: AES_256 // TLS_1.3</span>
        </div>
      </div>
    </div>
  </div>
`;
};
