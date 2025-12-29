import { Routes, Route } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import Settings from './pages/Settings';
import Health from './pages/Health';
import Updates from './pages/Updates';
import Activity from './pages/Activity';
import ErrorLog from './pages/ErrorLog';
import { ToastProvider } from './components/common';
import { ThemeProvider } from './contexts';

export default function App() {
  return (
    <ThemeProvider>
      <ToastProvider>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/health" element={<Health />} />
          <Route path="/updates" element={<Updates />} />
          <Route path="/activity" element={<Activity />} />
          <Route path="/errors" element={<ErrorLog />} />
          <Route path="/settings" element={<Settings />} />
        </Routes>
      </ToastProvider>
    </ThemeProvider>
  );
}
