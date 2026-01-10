import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Button,
  Badge,
  Switch,
  ConfirmModal,
  useToast,
  HelpTooltip,
  InfoPanel,
  Alert,
  SecurityAlert,
  DangerZone,
  DangerAction,
  SettingsSkeleton,
} from '@/components/common';
import { settingsApi } from '@/api';
import {
  Key,
  Shield,
  Copy,
  RefreshCw,
  Unlink,
  CheckCircle2,
  AlertTriangle,
  Eye,
  Download,
  BarChart3,
  Lock,
  ShieldCheck,
  Info,
  Sparkles,
  Cloud,
  Link2,
  Send,
} from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import type { Permissions } from '@/types';

export default function Settings() {
  const queryClient = useQueryClient();
  const toast = useToast();
  const [showDisconnectModal, setShowDisconnectModal] = useState(false);
  const [showRegenerateModal, setShowRegenerateModal] = useState(false);
  const [copied, setCopied] = useState(false);

  // Hub settings state
  const [hubUrl, setHubUrl] = useState('https://hub.peanutgraphic.com');
  const [hubApiKey, setHubApiKey] = useState('');
  const [showHubDisconnectModal, setShowHubDisconnectModal] = useState(false);

  const { data: settings, isLoading, error, refetch } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsApi.get,
  });

  const generateKeyMutation = useMutation({
    mutationFn: settingsApi.generateKey,
    onSuccess: () => {
      toast.success('Site key generated successfully');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Failed to generate site key');
    },
  });

  const regenerateKeyMutation = useMutation({
    mutationFn: settingsApi.regenerateKey,
    onSuccess: () => {
      toast.success('Site key regenerated. Make sure to update your manager site.');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      setShowRegenerateModal(false);
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Failed to regenerate site key');
    },
  });

  const disconnectMutation = useMutation({
    mutationFn: settingsApi.disconnect,
    onSuccess: () => {
      toast.success('Disconnected from manager site');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
      setShowDisconnectModal(false);
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Failed to disconnect');
    },
  });

  const updatePermissionsMutation = useMutation({
    mutationFn: (permissions: Partial<Permissions>) =>
      settingsApi.updatePermissions(permissions),
    onSuccess: () => {
      toast.success('Permissions updated');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Failed to update permissions');
    },
  });

  // Hub mutations
  const saveHubSettingsMutation = useMutation({
    mutationFn: () => settingsApi.saveHubSettings(hubUrl, hubApiKey),
    onSuccess: (data) => {
      toast.success(data.message || 'Hub connected successfully');
      setHubApiKey('');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Failed to connect to Hub');
    },
  });

  const testHubConnectionMutation = useMutation({
    mutationFn: settingsApi.testHubConnection,
    onSuccess: (data) => {
      toast.success(data.message || 'Hub connection successful');
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Hub connection failed');
    },
  });

  const disconnectHubMutation = useMutation({
    mutationFn: settingsApi.disconnectHub,
    onSuccess: () => {
      toast.success('Disconnected from Hub');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      setShowHubDisconnectModal(false);
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Failed to disconnect from Hub');
    },
  });

  const triggerHubSyncMutation = useMutation({
    mutationFn: settingsApi.triggerHubSync,
    onSuccess: (data) => {
      toast.success(data.message || 'Sync completed');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
    },
    onError: (err) => {
      toast.error((err as Error).message || 'Sync failed');
    },
  });

  const copyToClipboard = async (text: string) => {
    try {
      await navigator.clipboard.writeText(text);
      setCopied(true);
      toast.success('Copied to clipboard');
      setTimeout(() => setCopied(false), 2000);
    } catch {
      toast.error('Failed to copy to clipboard');
    }
  };

  const handlePermissionChange = (key: keyof Permissions, value: boolean) => {
    updatePermissionsMutation.mutate({ [key]: value });
  };

  if (isLoading) {
    return (
      <Layout title="Settings" description="Connection and permission settings">
        <SettingsSkeleton />
      </Layout>
    );
  }

  if (error) {
    return (
      <Layout title="Settings" description="Connection and permission settings">
        <Card>
          <div className="text-center py-8">
            <p className="text-slate-500 mb-4">{(error as Error).message}</p>
            <Button onClick={() => refetch()}>
              <RefreshCw className="w-4 h-4 mr-2" />
              Retry
            </Button>
          </div>
        </Card>
      </Layout>
    );
  }

  return (
    <Layout title="Settings" description="Connection and permission settings">
      {/* Security Overview */}
      <InfoPanel
        variant="info"
        title="About Peanut Connect Security"
        collapsible
        defaultOpen={false}
        className="mb-6"
      >
        <p>Peanut Connect uses secure authentication to protect your site:</p>
        <ul className="mt-2 text-sm space-y-1">
          <li className="flex items-start gap-2">
            <Lock className="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
            <span><strong>Site Key:</strong> A unique, cryptographically secure token that authenticates API requests</span>
          </li>
          <li className="flex items-start gap-2">
            <Shield className="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
            <span><strong>Permission Controls:</strong> Fine-grained control over what the manager can access</span>
          </li>
          <li className="flex items-start gap-2">
            <ShieldCheck className="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
            <span><strong>HTTPS Required:</strong> All communication is encrypted in transit</span>
          </li>
        </ul>
        <p className="mt-2 text-xs text-blue-600">
          Your site key should be treated like a password. Never share it publicly.
        </p>
      </InfoPanel>

      {/* Connection Status */}
      <Card className="mb-6">
        <CardHeader
          title={
            <span className="flex items-center gap-2">
              Connection Status
              <HelpTooltip content="Shows whether this site is connected to a Peanut Manager. When connected, the manager can monitor health and perform updates based on your permission settings." />
            </span>
          }
          action={
            settings?.connection.connected ? (
              <Badge variant="success">Connected</Badge>
            ) : (
              <Badge variant="warning">Not Connected</Badge>
            )
          }
        />
        {settings?.connection.connected ? (
          <div className="space-y-4">
            <div className="flex items-center gap-3 p-4 bg-green-50 rounded-lg border border-green-200">
              <CheckCircle2 className="w-6 h-6 text-green-600 flex-shrink-0" />
              <div className="flex-1 min-w-0">
                <p className="font-medium text-green-900">Connected to Manager</p>
                <p className="text-sm text-green-700 truncate">
                  {settings.connection.manager_url}
                </p>
              </div>
            </div>
            {settings.connection.last_sync && (
              <p className="text-sm text-slate-500">
                Last sync:{' '}
                {formatDistanceToNow(new Date(settings.connection.last_sync), {
                  addSuffix: true,
                })}
              </p>
            )}
            <InfoPanel variant="success" title="Connection Active" collapsible defaultOpen={false}>
              <p>Your site is being monitored. The manager can:</p>
              <ul className="mt-2 text-sm space-y-1">
                <li className="flex items-center gap-2">
                  <Eye className="w-3.5 h-3.5" />
                  View site health and status
                </li>
                <li className="flex items-center gap-2">
                  <Download className="w-3.5 h-3.5" />
                  See available updates
                </li>
                {settings.permissions.perform_updates && (
                  <li className="flex items-center gap-2">
                    <RefreshCw className="w-3.5 h-3.5" />
                    Install updates remotely
                  </li>
                )}
                {settings.permissions.access_analytics && settings.peanut_suite && (
                  <li className="flex items-center gap-2">
                    <BarChart3 className="w-3.5 h-3.5" />
                    Access Peanut Suite analytics
                  </li>
                )}
              </ul>
            </InfoPanel>
          </div>
        ) : (
          <div className="space-y-4">
            <div className="flex items-center gap-3 p-4 bg-amber-50 rounded-lg border border-amber-200">
              <AlertTriangle className="w-6 h-6 text-amber-600 flex-shrink-0" />
              <div>
                <p className="font-medium text-amber-900">Not Connected</p>
                <p className="text-sm text-amber-700">
                  Generate a site key below and add it to your manager site.
                </p>
              </div>
            </div>
            <InfoPanel variant="guide" title="How to Connect" collapsible defaultOpen={true}>
              <ol className="mt-2 text-sm space-y-2">
                <li className="flex items-start gap-2">
                  <span className="w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">1</span>
                  <span>Generate a site key by clicking the button below</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">2</span>
                  <span>Copy the site key and your site URL ({window.location.origin})</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">3</span>
                  <span>Go to your Peanut Manager dashboard and add a new site</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">4</span>
                  <span>Paste the site URL and site key to complete connection</span>
                </li>
              </ol>
            </InfoPanel>
          </div>
        )}
      </Card>

      {/* Site Key */}
      <Card className="mb-6">
        <CardHeader
          title={
            <span className="flex items-center gap-2">
              Site Key
              <HelpTooltip content="The site key is a secret token that authenticates your manager's API requests. Keep it secure - anyone with this key can access your site's health data and potentially perform updates." />
            </span>
          }
          description="Use this key to connect this site to your Peanut Monitor dashboard"
          action={<Key className="w-5 h-5 text-slate-400" />}
        />

        {!settings?.connection.site_key ? (
          <div className="space-y-4">
            <p className="text-slate-600">
              Generate a site key to enable remote monitoring from your Peanut Manager.
            </p>
            <Alert variant="info">
              <strong>What happens when you generate a key:</strong>
              <ul className="mt-1 text-sm space-y-0.5">
                <li>• A unique, secure token is created for this site</li>
                <li>• You can add this key to your manager to establish connection</li>
                <li>• The key can be regenerated at any time if compromised</li>
              </ul>
            </Alert>
            <Button
              onClick={() => generateKeyMutation.mutate()}
              loading={generateKeyMutation.isPending}
              icon={<Key className="w-4 h-4" />}
            >
              Generate Site Key
            </Button>
          </div>
        ) : (
          <div className="space-y-4">
            <SecurityAlert severity="low" title="Keep Your Site Key Secure">
              This key grants access to your site's API. Treat it like a password:
              <ul className="mt-1 text-sm space-y-0.5">
                <li>• Never share it in public channels or forums</li>
                <li>• Only give it to your Peanut Manager instance</li>
                <li>• Regenerate it if you suspect it's been compromised</li>
              </ul>
            </SecurityAlert>

            <div>
              <label className="block text-sm font-medium text-slate-700 mb-2">Your Site Key</label>
              <div className="flex items-center gap-2">
                <code className="flex-1 p-3 bg-slate-100 rounded-lg font-mono text-sm break-all border border-slate-200">
                  {settings.connection.site_key}
                </code>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => copyToClipboard(settings.connection.site_key!)}
                  icon={copied ? <CheckCircle2 className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                >
                  {copied ? 'Copied' : 'Copy'}
                </Button>
              </div>
            </div>

            <div className="p-3 bg-slate-50 rounded-lg border border-slate-200">
              <label className="block text-sm font-medium text-slate-700 mb-1">Your Site URL</label>
              <div className="flex items-center gap-2">
                <code className="flex-1 text-sm text-slate-600 font-mono">
                  {window.location.origin}
                </code>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => copyToClipboard(window.location.origin)}
                  icon={<Copy className="w-4 h-4" />}
                >
                  Copy
                </Button>
              </div>
            </div>
          </div>
        )}
      </Card>

      {/* Permissions */}
      <Card className="mb-6">
        <CardHeader
          title={
            <span className="flex items-center gap-2">
              Permissions
              <HelpTooltip content="Fine-grained control over what your manager site can do. You can enable or disable specific capabilities at any time." />
            </span>
          }
          description="Control what the manager site can do on this site"
          action={<Shield className="w-5 h-5 text-slate-400" />}
        />

        <InfoPanel variant="info" title="Understanding Permissions" collapsible defaultOpen={false} className="mb-4">
          <p>Permissions control what the connected manager can access and do:</p>
          <ul className="mt-2 text-sm space-y-1">
            <li><strong>Health Checks:</strong> View system info, PHP version, SSL status, etc.</li>
            <li><strong>List Updates:</strong> See which plugins/themes need updating</li>
            <li><strong>Perform Updates:</strong> Actually install updates remotely</li>
            <li><strong>Access Analytics:</strong> View Peanut Suite marketing data</li>
          </ul>
          <p className="mt-2 text-xs">Changes take effect immediately - no reconnection needed.</p>
        </InfoPanel>

        <div className="space-y-6">
          <div className="p-4 bg-slate-50 rounded-lg border border-slate-200">
            <div className="flex items-start justify-between gap-4">
              <div>
                <div className="flex items-center gap-2">
                  <Eye className="w-4 h-4 text-slate-600" />
                  <span className="font-medium text-slate-900">Health Checks</span>
                  <Badge variant="primary" size="sm">Always On</Badge>
                </div>
                <p className="text-sm text-slate-600 mt-1">
                  Allows manager to view site health status, PHP version, SSL info, and server configuration.
                </p>
              </div>
              <Switch
                checked={true}
                onChange={() => {}}
                disabled
              />
            </div>
          </div>

          <div className="p-4 bg-slate-50 rounded-lg border border-slate-200">
            <div className="flex items-start justify-between gap-4">
              <div>
                <div className="flex items-center gap-2">
                  <Download className="w-4 h-4 text-slate-600" />
                  <span className="font-medium text-slate-900">List Updates</span>
                  <Badge variant="primary" size="sm">Always On</Badge>
                </div>
                <p className="text-sm text-slate-600 mt-1">
                  Allows manager to see available plugin, theme, and core updates.
                </p>
              </div>
              <Switch
                checked={true}
                onChange={() => {}}
                disabled
              />
            </div>
          </div>

          <div className={`p-4 rounded-lg border ${settings?.permissions.perform_updates ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200'}`}>
            <div className="flex items-start justify-between gap-4">
              <div>
                <div className="flex items-center gap-2">
                  <RefreshCw className={`w-4 h-4 ${settings?.permissions.perform_updates ? 'text-amber-600' : 'text-slate-600'}`} />
                  <span className="font-medium text-slate-900">Perform Updates</span>
                  {settings?.permissions.perform_updates && (
                    <Badge variant="warning" size="sm">Caution</Badge>
                  )}
                </div>
                <p className="text-sm text-slate-600 mt-1">
                  Allow manager to install plugin, theme, and core updates remotely.
                </p>
                {settings?.permissions.perform_updates && (
                  <p className="text-xs text-amber-700 mt-2">
                    <AlertTriangle className="w-3 h-3 inline mr-1" />
                    The manager can update your site without confirmation. Only enable if you trust the manager.
                  </p>
                )}
              </div>
              <Switch
                checked={settings?.permissions.perform_updates ?? true}
                onChange={(checked) => handlePermissionChange('perform_updates', checked)}
              />
            </div>
          </div>

          <div className={`p-4 rounded-lg border ${settings?.permissions.access_analytics ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200'}`}>
            <div className="flex items-start justify-between gap-4">
              <div>
                <div className="flex items-center gap-2">
                  <BarChart3 className={`w-4 h-4 ${settings?.permissions.access_analytics ? 'text-blue-600' : 'text-slate-600'}`} />
                  <span className="font-medium text-slate-900">Access Analytics</span>
                </div>
                <p className="text-sm text-slate-600 mt-1">
                  Share Peanut Suite analytics data (UTM clicks, contacts, form submissions) with manager site.
                </p>
                {!settings?.peanut_suite && (
                  <p className="text-xs text-slate-500 mt-2">
                    <Info className="w-3 h-3 inline mr-1" />
                    Peanut Suite is not installed. This permission has no effect.
                  </p>
                )}
              </div>
              <Switch
                checked={settings?.permissions.access_analytics ?? true}
                onChange={(checked) => handlePermissionChange('access_analytics', checked)}
                disabled={!settings?.peanut_suite}
              />
            </div>
          </div>
        </div>
      </Card>

      {/* Hub Connection */}
      <Card className="mb-6">
        <CardHeader
          title={
            <span className="flex items-center gap-2">
              Hub Connection
              <HelpTooltip content="Connect to Peanut Hub to sync health data, analytics, and receive popup deployments from your agency dashboard." />
            </span>
          }
          action={<Cloud className="w-5 h-5 text-slate-400" />}
        />
        {settings?.hub?.connected ? (
          <div className="space-y-4">
            <div className="flex items-center gap-3 p-4 bg-green-50 rounded-lg border border-green-200">
              <CheckCircle2 className="w-6 h-6 text-green-600 flex-shrink-0" />
              <div className="flex-1 min-w-0">
                <p className="font-medium text-green-900">Connected to Hub</p>
                <p className="text-sm text-green-700 truncate">
                  {settings.hub.url}
                </p>
              </div>
            </div>
            {settings.hub.last_sync && (
              <p className="text-sm text-slate-500">
                Last sync:{' '}
                {formatDistanceToNow(new Date(settings.hub.last_sync), {
                  addSuffix: true,
                })}
              </p>
            )}
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={() => triggerHubSyncMutation.mutate()}
                loading={triggerHubSyncMutation.isPending}
                icon={<Send className="w-4 h-4" />}
              >
                Sync Now
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={() => testHubConnectionMutation.mutate()}
                loading={testHubConnectionMutation.isPending}
                icon={<RefreshCw className="w-4 h-4" />}
              >
                Test Connection
              </Button>
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setShowHubDisconnectModal(true)}
                icon={<Unlink className="w-4 h-4" />}
              >
                Disconnect
              </Button>
            </div>
          </div>
        ) : (
          <div className="space-y-4">
            <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
              <Cloud className="w-6 h-6 text-slate-400 flex-shrink-0" />
              <div>
                <p className="font-medium text-slate-700">Not Connected to Hub</p>
                <p className="text-sm text-slate-500">
                  Enter your Hub URL and API key to connect this site.
                </p>
              </div>
            </div>
            <InfoPanel variant="guide" title="How to Connect to Hub" collapsible defaultOpen={true}>
              <ol className="mt-2 text-sm space-y-2">
                <li className="flex items-start gap-2">
                  <span className="w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">1</span>
                  <span>Go to your Hub dashboard and find this site</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">2</span>
                  <span>Copy the API key from the site settings</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">3</span>
                  <span>Paste it below and click Connect</span>
                </li>
              </ol>
            </InfoPanel>
            <div className="space-y-3">
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Hub URL</label>
                <input
                  type="url"
                  value={hubUrl}
                  onChange={(e) => setHubUrl(e.target.value)}
                  placeholder="https://hub.peanutgraphic.com"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                <input
                  type="password"
                  value={hubApiKey}
                  onChange={(e) => setHubApiKey(e.target.value)}
                  placeholder="Enter your site's API key from Hub"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <Button
                onClick={() => saveHubSettingsMutation.mutate()}
                loading={saveHubSettingsMutation.isPending}
                disabled={!hubUrl || !hubApiKey}
                icon={<Link2 className="w-4 h-4" />}
              >
                Connect to Hub
              </Button>
            </div>
          </div>
        )}
      </Card>

      {/* Peanut Suite Integration */}
      <Card className="mb-6">
        <CardHeader
          title={
            <span className="flex items-center gap-2">
              Peanut Suite Integration
              <HelpTooltip content="Peanut Suite is a marketing toolkit for WordPress. When installed, you can sync analytics data with your manager for centralized reporting." />
            </span>
          }
          action={<Sparkles className="w-5 h-5 text-slate-400" />}
        />
        {settings?.peanut_suite ? (
          <div className="space-y-4">
            <div className="flex items-center gap-3 p-4 bg-green-50 rounded-lg border border-green-200">
              <CheckCircle2 className="w-6 h-6 text-green-600 flex-shrink-0" />
              <div>
                <p className="font-medium text-green-900">
                  Peanut Suite v{settings.peanut_suite.version} Detected
                </p>
                <p className="text-sm text-green-700">
                  Analytics data will be synced with your manager site when enabled.
                </p>
              </div>
            </div>
            {settings.peanut_suite.modules.length > 0 && (
              <div>
                <p className="text-sm font-medium text-slate-700 mb-2">Active Modules</p>
                <div className="flex flex-wrap gap-2">
                  {settings.peanut_suite.modules.map((module) => (
                    <Badge key={module} variant="primary" size="sm">
                      {module}
                    </Badge>
                  ))}
                </div>
              </div>
            )}
            <InfoPanel variant="tip" title="Analytics Syncing" collapsible defaultOpen={false}>
              <p>When analytics access is enabled, the manager can view:</p>
              <ul className="mt-2 text-sm space-y-1">
                <li>• UTM tracking data and campaign performance</li>
                <li>• Link click statistics</li>
                <li>• Contact form submissions</li>
                <li>• Popup impressions and conversions</li>
              </ul>
            </InfoPanel>
          </div>
        ) : (
          <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
            <div className="w-12 h-12 bg-slate-200 rounded-lg flex items-center justify-center">
              <Sparkles className="w-6 h-6 text-slate-400" />
            </div>
            <div>
              <p className="font-medium text-slate-700">Peanut Suite Not Installed</p>
              <p className="text-sm text-slate-500">
                Install Peanut Suite to enable marketing features and analytics syncing with your manager.
              </p>
            </div>
          </div>
        )}
      </Card>

      {/* Danger Zone */}
      {settings?.connection.site_key && (
        <DangerZone
          title="Danger Zone"
          description="These actions can disrupt your connection and require reconfiguration. Proceed with caution."
        >
          <DangerAction
            icon={<RefreshCw className="w-5 h-5" />}
            title="Regenerate Site Key"
            description="Create a new site key and invalidate the current one. You'll need to update your manager."
            buttonLabel="Regenerate"
            warningMessage="This will immediately invalidate your current site key. The manager will lose access until you update it with the new key. Make sure you have access to your manager dashboard before proceeding."
            onAction={async () => { await regenerateKeyMutation.mutateAsync(); }}
            loading={regenerateKeyMutation.isPending}
          />

          {settings.connection.connected && (
            <DangerAction
              icon={<Unlink className="w-5 h-5" />}
              title="Disconnect from Manager"
              description="Remove the connection to your manager site. Your site key will remain valid for reconnection."
              buttonLabel="Disconnect"
              warningMessage="This will disconnect your site from the manager. You won't receive remote monitoring or updates until you reconnect. Your site key will remain valid, so you can reconnect by adding this site to your manager again."
              onAction={async () => { await disconnectMutation.mutateAsync(); }}
              loading={disconnectMutation.isPending}
            />
          )}
        </DangerZone>
      )}

      {/* Disconnect Modal */}
      <ConfirmModal
        isOpen={showDisconnectModal}
        onClose={() => setShowDisconnectModal(false)}
        onConfirm={() => disconnectMutation.mutate()}
        title="Disconnect from Manager"
        message="Are you sure you want to disconnect from your manager site? You'll need to add the site key again to reconnect."
        confirmText="Disconnect"
        variant="danger"
        loading={disconnectMutation.isPending}
      />

      {/* Regenerate Key Modal */}
      <ConfirmModal
        isOpen={showRegenerateModal}
        onClose={() => setShowRegenerateModal(false)}
        onConfirm={() => regenerateKeyMutation.mutate()}
        title="Regenerate Site Key"
        message="Are you sure you want to regenerate the site key? The current key will be invalidated and you'll need to update your manager site with the new key."
        confirmText="Regenerate"
        variant="danger"
        loading={regenerateKeyMutation.isPending}
      />

      {/* Hub Disconnect Modal */}
      <ConfirmModal
        isOpen={showHubDisconnectModal}
        onClose={() => setShowHubDisconnectModal(false)}
        onConfirm={() => disconnectHubMutation.mutate()}
        title="Disconnect from Hub"
        message="Are you sure you want to disconnect from Peanut Hub? Health data will no longer be synced and you'll need to reconfigure the connection to reconnect."
        confirmText="Disconnect"
        variant="danger"
        loading={disconnectHubMutation.isPending}
      />
    </Layout>
  );
}
