export interface EmailFormData {
    clientName: string;
    clientEmail: string;
    subject: string;
    message: string;
}

export const generateEmailTemplate = (form: EmailFormData): string => {
    const currentDate = new Date().toISOString().split('T')[0];

    return `
  <div style="display:none; max-height:0px; max-width:0px; opacity:0; overflow:hidden; font-size:1px; line-height:1px; color:#080b2a;">
    ${form.subject || 'Subject Preview'} &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
  </div>
  <div style="background: radial-gradient(circle at 50% 0%, #1a224d 0%, #080b2a 100%); color: #ffffff; padding: 0; font-family: 'Inter', Arial, sans-serif; width: 1000px; margin: 20px auto; border: 1px solid rgba(0, 247, 255, 0.25); box-sizing: border-box; border-radius: 12px; overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.7);">
    <!-- Glowing Top Bar -->
    <div style="height: 4px; background: linear-gradient(90deg, transparent, #00f7ff, transparent); box-shadow: 0 0 15px #00f7ff;"></div>
    
    <div style="padding: 25px 40px;">
      <!-- HUD System Header -->
      <div style="border-bottom: 1px solid rgba(0, 247, 255, 0.3); padding-bottom: 12px; margin-bottom: 20px; display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: middle; width: 65%;">
          <span style="color: #00f7ff; font-family: 'Orbitron', sans-serif; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 4px; text-shadow: 0 0 10px rgba(0, 247, 255, 0.5);">felipemiramontesr&zwnj;.net</span>
        </div>
        <div style="display: table-cell; text-align: right; vertical-align: middle; width: 35%; color: #7e8ec2; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px; white-space: nowrap;">
          LOG_STREAM // ${currentDate}
        </div>
      </div>

      <!-- Main Interface Card (Upper Glass) -->
      <div style="background: rgba(255, 255, 255, 0.07); padding: 20px 30px; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.1); position: relative; box-shadow: inset 0 0 50px rgba(0, 247, 255, 0.05); margin-bottom: 15px;">
        
        <!-- Sender Info (Unified Style) -->
        <div style="margin-bottom: 12px;">
          <div style="margin-bottom: 4px;">
            <span style="color: #7e8ec2; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px; text-transform: uppercase;">👤 ORIGIN_POINT</span>
          </div>
          <div style="background: rgba(0, 247, 255, 0.08); border-left: 4px solid #00f7ff; padding: 10px 15px; border-radius: 0 8px 8px 0; box-shadow: 0 0 20px rgba(0, 247, 255, 0.05);">
            <p style="margin: 0; color: #00f7ff; font-size: 15px; font-weight: 600; text-shadow: 0 0 15px rgba(0, 247, 255, 0.4); text-transform: uppercase; letter-spacing: 1px; line-height: 22px;">
              ${form.clientName}
            </p>
          </div>
        </div>
        
        <!-- Subject Info (Unified Style) -->
        <div style="margin-bottom: 12px;">
          <div style="margin-bottom: 4px;">
            <span style="color: #7e8ec2; font-family: 'Orbitron', sans-serif; font-size: 9px; letter-spacing: 2px; text-transform: uppercase;">🛰️ TRANSMISSION_SUBJECT</span>
          </div>
          <div style="background: rgba(0, 247, 255, 0.1); border-left: 4px solid #00f7ff; padding: 10px 15px; border-radius: 0 8px 8px 0; box-shadow: 0 0 25px rgba(0, 247, 255, 0.1);">
            <p style="margin: 0; color: #00f7ff; font-size: 18px; font-weight: 500; font-family: 'Inter', sans-serif; text-shadow: 0 0 20px rgba(0, 247, 255, 0.3); letter-spacing: 0.5px; text-transform: none; line-height: 22px;">
              ${form.subject || '[ Subject missing ]'}
            </p>
          </div>
        </div>
        
        <!-- Content Section -->
        <div style="border-top: 1px solid rgba(0, 247, 255, 0.2); padding-top: 20px; margin-top: 10px;">
          <div style="margin-bottom: 8px;">
            <span style="color: #ffffff; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">📥 SIGNAL_DATA</span>
          </div>
          <div style="color: #ffffff; line-height: 1.6; font-size: 15px; padding: 25px; background: rgba(0, 0, 0, 0.5); border-radius: 12px; border: 1px solid rgba(0, 247, 255, 0.3); box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.8), 0 10px 30px rgba(0,0,0,0.5); position: relative;">
            <div style="color: rgba(0, 247, 255, 0.4); font-family: 'Orbitron', sans-serif; font-size: 8px; margin-bottom: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 8px; letter-spacing: 2px; text-align: left;">
              // [ START_TRANSMISSION ]
            </div>
            
            <div style="white-space: pre-wrap; word-break: break-word; min-height: 100px;">${form.message || 'Waiting for signal input...'}</div>

            <div style="color: rgba(0, 247, 255, 0.4); font-family: 'Orbitron', sans-serif; font-size: 8px; margin-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 10px; text-align: left; letter-spacing: 2px;">
              // [ END_SIGNAL ]
            </div>
          </div>
        </div>
      </div>
      
      <!-- Technical Footer -->
      <div style="margin-top: 15px; display: table; width: 100%; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 15px;">
        <div style="display: table-cell; vertical-align: middle; text-align: left;">
          <span style="color: #00f7ff; font-size: 10px; font-family: 'Orbitron', sans-serif; letter-spacing: 2px; font-weight: 600; text-transform: uppercase;">🛡️ INTEGRITY: OPTIMAL</span>
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
          <span style="color: #7e8ec2; font-size: 9px; font-family: 'Orbitron', sans-serif; opacity: 0.8; letter-spacing: 1.5px; text-transform: uppercase;">PROTO: AES_256 // TLS_1.3</span>
        </div>
      </div>
    </div>
  </div>
`;
};
