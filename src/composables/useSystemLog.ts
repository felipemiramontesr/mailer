import { ref } from 'vue';

export interface LogEntry {
  id: string;
  timestamp: string;
  level: 'SYSTEM' | 'NETWORK' | 'AUTH' | 'AI' | 'CRYPTO' | 'SHRED';
  message: string;
}

/**
 * Composable for tracking real-time system events in a professional HUD terminal.
 */
export function useSystemLog() {
  const logs = ref<LogEntry[]>([]);
  const MAX_LOGS = 50;

  const addLog = (level: LogEntry['level'], message: string) => {
    const entry: LogEntry = {
      id: Math.random().toString(36).substring(2, 9),
      timestamp: new Date().toLocaleTimeString('en-GB', { hour12: false }),
      level,
      message,
    };

    logs.value.unshift(entry);
    if (logs.value.length > MAX_LOGS) {
      logs.value.pop();
    }
  };

  const clearLogs = () => {
    logs.value = [];
  };

  // Initial Boot Sequence Logs
  addLog('SYSTEM', 'INIT_SEQUENCE_START');
  addLog('SYSTEM', 'ENCRYPTION_ENGINE_STABLE');
  addLog('NETWORK', 'PONG: SECURE_PH_NODE_V3.0');

  return {
    logs,
    addLog,
    clearLogs,
  };
}
