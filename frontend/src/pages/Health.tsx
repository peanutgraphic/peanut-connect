import { useState, useRef, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Badge,
  Button,
  HelpTooltip,
  InfoPanel,
  Alert,
  SecurityAlert,
  Recommendation,
  HealthSkeleton,
} from '@/components/common';
import { healthApi } from '@/api';
import { exportAsJson, exportAsText, exportAsHtml, printReport } from '@/utils/export';
import {
  CheckCircle2,
  XCircle,
  AlertTriangle,
  Server,
  Database,
  HardDrive,
  Shield,
  RefreshCw,
  Globe,
  Cpu,
  Lock,
  Archive,
  Info,
  Download,
  FileJson,
  FileText,
  FileCode,
  Printer,
  ChevronDown,
} from 'lucide-react';

// Health metric explanations
const healthExplanations = {
  wordpress: {
    title: 'WordPress Version',
    description: 'The version of WordPress core running on your site.',
    recommendation: 'Always keep WordPress updated to the latest version for security patches and new features.',
    why: 'Outdated WordPress versions are the #1 target for hackers. Most security vulnerabilities are patched in updates.',
  },
  php: {
    title: 'PHP Version',
    description: 'The PHP version running on your server.',
    recommendation: 'PHP 8.0+ is recommended for best performance and security. PHP 7.4 reached end-of-life.',
    why: 'Newer PHP versions are faster and more secure. Old versions may have known vulnerabilities.',
  },
  ssl: {
    title: 'SSL Certificate',
    description: 'Encrypts data between your site and visitors.',
    recommendation: 'Always use HTTPS. Renew certificates before they expire to avoid browser warnings.',
    why: 'SSL protects sensitive data, improves SEO rankings, and is required for modern browser features.',
  },
  plugins: {
    title: 'Plugins',
    description: 'WordPress plugins extend site functionality.',
    recommendation: 'Keep plugins updated, remove unused ones, and only install from trusted sources.',
    why: 'Outdated or vulnerable plugins are a common attack vector. Fewer plugins = less risk.',
  },
  themes: {
    title: 'Themes',
    description: 'WordPress themes control your site appearance.',
    recommendation: 'Keep your active theme updated. Remove unused themes to reduce attack surface.',
    why: 'Theme vulnerabilities can expose your entire site. Delete themes you don\'t use.',
  },
  disk: {
    title: 'Disk Space',
    description: 'Storage available on your server.',
    recommendation: 'Keep at least 20% free space for backups, updates, and caching.',
    why: 'Running out of disk space can crash your site, prevent backups, and break updates.',
  },
  database: {
    title: 'Database',
    description: 'MySQL/MariaDB stores all your site content.',
    recommendation: 'Regularly optimize database tables and clean post revisions.',
    why: 'A bloated database slows down your site. Regular maintenance keeps it fast.',
  },
  permissions: {
    title: 'File Permissions',
    description: 'Controls who can read/write files on your server.',
    recommendation: 'wp-config.php should be 600 or 644. Directories 755, files 644.',
    why: 'Incorrect permissions can allow hackers to modify critical files or inject malware.',
  },
  server: {
    title: 'Server Configuration',
    description: 'PHP and server settings that affect performance.',
    recommendation: 'Memory limit 256M+, max upload 64M+, execution time 30s+.',
    why: 'Low limits can cause plugin failures, upload errors, and timeout issues.',
  },
  backup: {
    title: 'Backups',
    description: 'Copies of your site for disaster recovery.',
    recommendation: 'Backup daily, store off-site, and test restores regularly.',
    why: 'Without backups, any disaster (hack, crash, mistake) could mean total data loss.',
  },
};

export default function Health() {
  const { data: health, isLoading, error, refetch, isFetching } = useQuery({
    queryKey: ['health'],
    queryFn: healthApi.get,
  });

  const [showExportMenu, setShowExportMenu] = useState(false);
  const exportMenuRef = useRef<HTMLDivElement>(null);

  // Close export menu when clicking outside
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (exportMenuRef.current && !exportMenuRef.current.contains(event.target as Node)) {
        setShowExportMenu(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleExport = (format: 'json' | 'text' | 'html' | 'print') => {
    if (!health) return;
    const siteUrl = window.location.origin;

    switch (format) {
      case 'json':
        exportAsJson(health);
        break;
      case 'text':
        exportAsText(health, siteUrl);
        break;
      case 'html':
        exportAsHtml(health, siteUrl);
        break;
      case 'print':
        printReport(health, siteUrl);
        break;
    }
    setShowExportMenu(false);
  };

  const StatusIcon = ({ status }: { status: boolean }) => {
    return status ? (
      <CheckCircle2 className="w-5 h-5 text-green-500" />
    ) : (
      <XCircle className="w-5 h-5 text-red-500" />
    );
  };

  const WarningIcon = ({ warning }: { warning: boolean }) => {
    return warning ? (
      <AlertTriangle className="w-5 h-5 text-amber-500" />
    ) : (
      <CheckCircle2 className="w-5 h-5 text-green-500" />
    );
  };

  // Calculate overall health score
  const calculateHealthScore = () => {
    if (!health) return 0;
    let score = 100;

    // WordPress updates (-15)
    if (health.wp_version?.needs_update) score -= 15;

    // PHP version (-10 if not recommended, -20 if below minimum)
    if (!health.php_version?.recommended) score -= 10;
    if (!health.php_version?.minimum_met) score -= 20;

    // SSL issues (-25)
    if (!health.ssl?.enabled) score -= 25;
    if (health.ssl?.enabled && !health.ssl?.valid) score -= 15;
    if (health.ssl?.days_until_expiry !== null && health.ssl.days_until_expiry < 14) score -= 10;

    // Plugin updates (-2 per update, max -20)
    score -= Math.min((health.plugins?.updates_available || 0) * 2, 20);

    // Theme updates (-5 per update, max -10)
    score -= Math.min((health.themes?.updates_available || 0) * 5, 10);

    // Disk space (-10 if over 80%, -20 if over 90%)
    if (health.disk_space?.used_percent && health.disk_space.used_percent > 90) score -= 20;
    else if (health.disk_space?.used_percent && health.disk_space.used_percent > 80) score -= 10;

    // File permissions (-10)
    if (!health.file_permissions?.secure) score -= 10;

    // Debug mode (-5)
    if (health.debug_mode) score -= 5;

    // Backup issues (-10 if no plugin, -5 if old backup)
    if (!health.backup?.plugin_detected) score -= 10;
    if (health.backup?.days_since_last !== null && health.backup.days_since_last > 7) score -= 5;

    return Math.max(0, score);
  };

  const getScoreColor = (score: number) => {
    if (score >= 90) return 'text-green-600';
    if (score >= 70) return 'text-amber-600';
    return 'text-red-600';
  };

  const getScoreBg = (score: number) => {
    if (score >= 90) return 'bg-green-100';
    if (score >= 70) return 'bg-amber-100';
    return 'bg-red-100';
  };

  if (isLoading) {
    return (
      <Layout title="Health Check" description="System health and status">
        <HealthSkeleton />
      </Layout>
    );
  }

  if (error) {
    return (
      <Layout title="Health Check" description="System health and status">
        <Card>
          <div className="text-center py-8">
            <XCircle className="w-12 h-12 text-red-500 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-slate-900 mb-2">
              Failed to load health data
            </h3>
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

  const healthScore = calculateHealthScore();
  const criticalIssues = [];
  const warnings = [];

  // Categorize issues
  if (health?.wp_version?.needs_update) warnings.push('WordPress needs updating');
  if (!health?.php_version?.recommended) warnings.push('PHP version is not optimal');
  if (!health?.php_version?.minimum_met) criticalIssues.push('PHP version is below minimum requirements');
  if (!health?.ssl?.enabled) criticalIssues.push('SSL is not enabled');
  if (health?.ssl?.enabled && !health?.ssl?.valid) criticalIssues.push('SSL certificate is invalid');
  if (health?.ssl?.days_until_expiry !== null && health?.ssl?.days_until_expiry !== undefined && health.ssl.days_until_expiry < 7) criticalIssues.push('SSL certificate expires soon');
  if ((health?.plugins?.updates_available || 0) > 0) warnings.push(`${health?.plugins?.updates_available} plugin updates available`);
  if ((health?.themes?.updates_available || 0) > 0) warnings.push(`${health?.themes?.updates_available} theme updates available`);
  if (!health?.file_permissions?.secure) warnings.push('File permissions may be insecure');
  if (health?.debug_mode) warnings.push('Debug mode is enabled');
  if (!health?.backup?.plugin_detected) warnings.push('No backup plugin detected');
  if (health?.disk_space?.used_percent && health.disk_space.used_percent > 80) warnings.push('Disk space is running low');

  return (
    <Layout title="Health Check" description="System health and status">
      {/* Health Score Overview */}
      <Card className="mb-6">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div className="flex items-center gap-4">
            <div className={`w-20 h-20 rounded-full ${getScoreBg(healthScore)} flex items-center justify-center`}>
              <span className={`text-3xl font-bold ${getScoreColor(healthScore)}`}>
                {healthScore}
              </span>
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-900">Health Score</h2>
              <p className="text-sm text-slate-600">
                {healthScore >= 90 ? 'Excellent! Your site is in great shape.' :
                 healthScore >= 70 ? 'Good, but there are some issues to address.' :
                 'Needs attention! Several issues require immediate action.'}
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Button onClick={() => refetch()} loading={isFetching} variant="outline">
              <RefreshCw className="w-4 h-4 mr-2" />
              Refresh
            </Button>

            {/* Export Dropdown */}
            <div className="relative" ref={exportMenuRef}>
              <Button
                variant="outline"
                onClick={() => setShowExportMenu(!showExportMenu)}
              >
                <Download className="w-4 h-4 mr-2" />
                Export
                <ChevronDown className="w-4 h-4 ml-1" />
              </Button>

              {showExportMenu && (
                <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50">
                  <button
                    onClick={() => handleExport('json')}
                    className="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                  >
                    <FileJson className="w-4 h-4 text-blue-500" />
                    Export as JSON
                  </button>
                  <button
                    onClick={() => handleExport('text')}
                    className="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                  >
                    <FileText className="w-4 h-4 text-slate-500" />
                    Export as Text
                  </button>
                  <button
                    onClick={() => handleExport('html')}
                    className="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                  >
                    <FileCode className="w-4 h-4 text-orange-500" />
                    Export as HTML
                  </button>
                  <div className="border-t border-slate-100 my-1" />
                  <button
                    onClick={() => handleExport('print')}
                    className="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                  >
                    <Printer className="w-4 h-4 text-slate-500" />
                    Print / Save as PDF
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      </Card>

      {/* Critical Issues Alert */}
      {criticalIssues.length > 0 && (
        <SecurityAlert severity="critical" title="Critical Issues Found" className="mb-6">
          <p className="mb-2">These issues require immediate attention:</p>
          <ul className="list-disc list-inside space-y-1">
            {criticalIssues.map((issue, i) => (
              <li key={i}>{issue}</li>
            ))}
          </ul>
        </SecurityAlert>
      )}

      {/* Warnings */}
      {warnings.length > 0 && criticalIssues.length === 0 && (
        <Alert variant="warning" title={`${warnings.length} Issue${warnings.length > 1 ? 's' : ''} Detected`} className="mb-6">
          <ul className="mt-2 space-y-1">
            {warnings.slice(0, 5).map((warning, i) => (
              <li key={i} className="flex items-center gap-2">
                <span className="w-1.5 h-1.5 bg-amber-500 rounded-full" />
                {warning}
              </li>
            ))}
            {warnings.length > 5 && (
              <li className="text-sm text-amber-600">+{warnings.length - 5} more</li>
            )}
          </ul>
        </Alert>
      )}

      {/* All Good Message */}
      {criticalIssues.length === 0 && warnings.length === 0 && (
        <InfoPanel variant="success" title="All Systems Healthy" className="mb-6">
          Your WordPress site is in excellent condition. Keep up the good work by maintaining regular updates and backups.
        </InfoPanel>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* WordPress & PHP */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                WordPress & PHP
                <HelpTooltip content={healthExplanations.wordpress.why} />
              </span>
            }
            action={
              <div className="flex items-center gap-2">
                <Globe className="w-5 h-5 text-slate-400" />
              </div>
            }
          />
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">WordPress Version</span>
                <HelpTooltip content="The core WordPress software version. Updates include security patches, bug fixes, and new features." />
              </div>
              <div className="flex items-center gap-2">
                <span className="font-medium">{health?.wp_version?.version}</span>
                {health?.wp_version?.needs_update ? (
                  <Badge variant="warning" size="sm">Update to {health?.wp_version?.latest_version}</Badge>
                ) : (
                  <Badge variant="success" size="sm">Current</Badge>
                )}
              </div>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">PHP Version</span>
                <HelpTooltip content="PHP is the programming language WordPress uses. Newer versions are faster and more secure. PHP 8.0+ is recommended." />
              </div>
              <div className="flex items-center gap-2">
                <span className="font-medium">{health?.php_version?.version}</span>
                {health?.php_version?.recommended ? (
                  <Badge variant="success" size="sm">Recommended</Badge>
                ) : health?.php_version?.minimum_met ? (
                  <Badge variant="warning" size="sm">Consider Upgrading</Badge>
                ) : (
                  <Badge variant="danger" size="sm">Upgrade Required</Badge>
                )}
              </div>
            </div>

            {health?.wp_version?.needs_update && (
              <Recommendation title="Update WordPress">
                WordPress {health.wp_version.latest_version} is available. Updates include security fixes that protect against known vulnerabilities.
              </Recommendation>
            )}

            {!health?.php_version?.recommended && health?.php_version?.minimum_met && (
              <InfoPanel variant="tip" title="PHP Upgrade Recommended" collapsible defaultOpen={false}>
                <p>Your PHP version works, but upgrading to PHP 8.0+ will:</p>
                <ul className="mt-2 text-sm space-y-1">
                  <li>• Improve site speed by 20-30%</li>
                  <li>• Receive security updates</li>
                  <li>• Enable modern WordPress features</li>
                </ul>
                <p className="mt-2 text-xs">Contact your hosting provider to upgrade PHP.</p>
              </InfoPanel>
            )}
          </div>
        </Card>

        {/* SSL Certificate */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                SSL Certificate
                <HelpTooltip content={healthExplanations.ssl.why} />
              </span>
            }
            action={<Lock className="w-5 h-5 text-slate-400" />}
          />
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">SSL Enabled</span>
                <HelpTooltip content="HTTPS encrypts all data between your visitors and your server, protecting passwords, payment info, and personal data." />
              </div>
              <div className="flex items-center gap-2">
                <StatusIcon status={health?.ssl?.enabled || false} />
                <span className="font-medium">{health?.ssl?.enabled ? 'Yes' : 'No'}</span>
              </div>
            </div>
            {health?.ssl?.enabled && (
              <>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <span className="text-sm text-slate-600">Certificate Valid</span>
                    <HelpTooltip content="An invalid certificate will show security warnings in browsers, scaring away visitors and hurting SEO." />
                  </div>
                  <div className="flex items-center gap-2">
                    <StatusIcon status={health?.ssl?.valid || false} />
                    <span className="font-medium">{health?.ssl?.valid ? 'Yes' : 'No'}</span>
                  </div>
                </div>
                {health?.ssl?.issuer && (
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-slate-600">Issuer</span>
                    <span className="font-medium text-sm">{health.ssl.issuer}</span>
                  </div>
                )}
                {health?.ssl?.days_until_expiry !== null && (
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="text-sm text-slate-600">Days Until Expiry</span>
                      <HelpTooltip content="SSL certificates expire and must be renewed. Most should auto-renew, but check to avoid surprises." />
                    </div>
                    <Badge
                      variant={
                        (health.ssl.days_until_expiry ?? 0) > 30
                          ? 'success'
                          : (health.ssl.days_until_expiry ?? 0) > 7
                          ? 'warning'
                          : 'danger'
                      }
                    >
                      {health.ssl.days_until_expiry} days
                    </Badge>
                  </div>
                )}
              </>
            )}

            {!health?.ssl?.enabled && (
              <SecurityAlert severity="critical" title="SSL Not Enabled">
                Your site is not using HTTPS. This means:
                <ul className="mt-2 text-sm space-y-1">
                  <li>• Visitor data is transmitted unencrypted</li>
                  <li>• Browsers show "Not Secure" warnings</li>
                  <li>• Search engines penalize your rankings</li>
                  <li>• Payment processing is impossible</li>
                </ul>
                <p className="mt-2 text-sm font-medium">Contact your hosting provider to enable SSL immediately.</p>
              </SecurityAlert>
            )}

            {health?.ssl?.days_until_expiry !== null && health?.ssl?.days_until_expiry !== undefined && health.ssl.days_until_expiry < 14 && (
              <Alert variant="warning" title="Certificate Expiring Soon">
                Your SSL certificate expires in {health?.ssl?.days_until_expiry} days. Ensure auto-renewal is configured or renew manually to avoid browser warnings.
              </Alert>
            )}
          </div>
        </Card>

        {/* Plugins & Themes */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Plugins & Themes
                <HelpTooltip content={healthExplanations.plugins.why} />
              </span>
            }
            action={<Cpu className="w-5 h-5 text-slate-400" />}
          />
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Total Plugins</span>
                <HelpTooltip content="Active plugins add functionality but also increase security risk and slow down your site. Only keep what you need." />
              </div>
              <span className="font-medium">
                {health?.plugins?.active} active / {health?.plugins?.total} total
              </span>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Plugin Updates</span>
                <HelpTooltip content="Outdated plugins are a major security risk. Most hacks exploit known vulnerabilities in old plugin versions." />
              </div>
              <div className="flex items-center gap-2">
                <WarningIcon warning={(health?.plugins?.updates_available || 0) > 0} />
                <span className="font-medium">{health?.plugins?.updates_available || 0}</span>
              </div>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Active Theme</span>
                <HelpTooltip content="Your current WordPress theme. Keep it updated and remove unused themes." />
              </div>
              <span className="font-medium">{health?.themes?.active}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-slate-600">Theme Updates</span>
              <div className="flex items-center gap-2">
                <WarningIcon warning={(health?.themes?.updates_available || 0) > 0} />
                <span className="font-medium">{health?.themes?.updates_available || 0}</span>
              </div>
            </div>

            {(health?.plugins?.inactive || 0) > 2 && (
              <InfoPanel variant="tip" title="Inactive Plugins Detected" collapsible defaultOpen={false}>
                You have {health?.plugins?.inactive} inactive plugins. Consider deleting plugins you don't use:
                <ul className="mt-2 text-sm space-y-1">
                  <li>• Inactive plugins can still have security vulnerabilities</li>
                  <li>• They take up disk space and backup size</li>
                  <li>• Database tables may remain even when deactivated</li>
                </ul>
              </InfoPanel>
            )}

            {(health?.plugins?.updates_available || 0) > 3 && (
              <Alert variant="warning">
                You have {health?.plugins?.updates_available} plugin updates pending. Update regularly to stay secure.
              </Alert>
            )}
          </div>
        </Card>

        {/* Disk Space */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Disk Space
                <HelpTooltip content={healthExplanations.disk.why} />
              </span>
            }
            action={<HardDrive className="w-5 h-5 text-slate-400" />}
          />
          {health?.disk_space?.available ? (
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">Total</span>
                <span className="font-medium">{health?.disk_space?.total_formatted}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">Used</span>
                <span className="font-medium">{health?.disk_space?.used_formatted}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">Free</span>
                <span className="font-medium">{health?.disk_space?.free_formatted}</span>
              </div>
              <div className="mt-2">
                <div className="flex items-center justify-between mb-1">
                  <span className="text-xs text-slate-500">Usage</span>
                  <span className="text-xs text-slate-500">
                    {health?.disk_space?.used_percent}%
                  </span>
                </div>
                <div className="w-full bg-slate-200 rounded-full h-2.5">
                  <div
                    className={`h-2.5 rounded-full transition-all ${
                      (health?.disk_space?.used_percent || 0) > 90
                        ? 'bg-red-500'
                        : (health?.disk_space?.used_percent || 0) > 70
                        ? 'bg-amber-500'
                        : 'bg-green-500'
                    }`}
                    style={{ width: `${health?.disk_space?.used_percent || 0}%` }}
                  />
                </div>
              </div>

              {(health?.disk_space?.used_percent || 0) > 80 && (
                <Alert variant={(health?.disk_space?.used_percent || 0) > 90 ? 'error' : 'warning'}>
                  {(health?.disk_space?.used_percent || 0) > 90 ? (
                    <>
                      <strong>Critical:</strong> Disk space is almost full! This can cause backup failures, update errors, and site crashes.
                    </>
                  ) : (
                    <>
                      Disk space is getting low. Consider cleaning up unused files, optimizing images, or upgrading your hosting plan.
                    </>
                  )}
                </Alert>
              )}
            </div>
          ) : (
            <div className="p-4 bg-slate-50 rounded-lg">
              <div className="flex items-center gap-2 text-slate-500">
                <Info className="w-5 h-5" />
                <p className="text-sm">Disk space information is not available from your hosting provider.</p>
              </div>
            </div>
          )}
        </Card>

        {/* Database */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Database
                <HelpTooltip content={healthExplanations.database.why} />
              </span>
            }
            action={<Database className="w-5 h-5 text-slate-400" />}
          />
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Database Size</span>
                <HelpTooltip content="Total size of all WordPress database tables. Large databases can slow down queries and backups." />
              </div>
              <span className="font-medium">{health?.database?.size_formatted}</span>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Tables</span>
                <HelpTooltip content="Number of database tables. More tables = more complexity. Old plugins may leave orphaned tables." />
              </div>
              <span className="font-medium">{health?.database?.tables_count}</span>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Table Prefix</span>
                <HelpTooltip content="Database table prefix. Using 'wp_' is common but using a custom prefix adds a small security benefit." />
              </div>
              <code className="font-mono text-sm bg-slate-100 px-2 py-0.5 rounded">
                {health?.database?.prefix}
              </code>
            </div>

            {health?.database?.prefix === 'wp_' && (
              <InfoPanel variant="info" title="Default Table Prefix" collapsible defaultOpen={false}>
                You're using the default 'wp_' table prefix. While this works fine, a custom prefix provides a minor security benefit against automated SQL injection attacks.
                <p className="mt-2 text-xs">Note: Changing this on an existing site requires database migration.</p>
              </InfoPanel>
            )}
          </div>
        </Card>

        {/* Server */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Server
                <HelpTooltip content={healthExplanations.server.why} />
              </span>
            }
            action={<Server className="w-5 h-5 text-slate-400" />}
          />
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Software</span>
                <HelpTooltip content="Your web server software (Apache, Nginx, LiteSpeed). Each has different performance characteristics." />
              </div>
              <span className="font-medium text-right max-w-[200px] truncate">{health?.server?.software}</span>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Memory Limit</span>
                <HelpTooltip content="Maximum memory PHP can use. Low limits cause 'out of memory' errors on complex pages or during imports." />
              </div>
              <div className="flex items-center gap-2">
                <span className="font-medium">{health?.server?.memory_limit}</span>
                {parseInt(health?.server?.memory_limit || '0') < 128 && (
                  <Badge variant="warning" size="sm">Low</Badge>
                )}
              </div>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Max Upload</span>
                <HelpTooltip content="Maximum file upload size. Affects media uploads, plugin/theme uploads, and import tools." />
              </div>
              <span className="font-medium">{health?.server?.max_upload_size_formatted}</span>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Max Execution Time</span>
                <HelpTooltip content="How long scripts can run before timing out. Long operations like imports need higher values." />
              </div>
              <span className="font-medium">{health?.server?.max_execution_time}s</span>
            </div>

            {/* PHP Extensions */}
            {health?.server?.php_extensions && (
              <div className="pt-2 border-t border-slate-100">
                <div className="flex items-center gap-2 mb-2">
                  <span className="text-xs font-medium text-slate-500 uppercase tracking-wide">PHP Extensions</span>
                  <HelpTooltip content="Required PHP extensions for WordPress features. Missing extensions may cause plugin compatibility issues." />
                </div>
                <div className="flex flex-wrap gap-2">
                  {Object.entries(health.server.php_extensions).map(([ext, enabled]) => (
                    <span
                      key={ext}
                      className={`text-xs px-2 py-1 rounded ${
                        enabled
                          ? 'bg-green-100 text-green-700'
                          : 'bg-red-100 text-red-700'
                      }`}
                    >
                      {ext}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </div>
        </Card>

        {/* File Permissions */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                File Permissions
                <HelpTooltip content={healthExplanations.permissions.why} />
              </span>
            }
            action={<Shield className="w-5 h-5 text-slate-400" />}
          />
          <div className="space-y-4">
            {!health?.file_permissions?.secure && (
              <SecurityAlert severity="medium" title="Permission Issues Detected">
                Some file permissions may be too permissive. This could allow unauthorized file modifications.
              </SecurityAlert>
            )}

            {health?.file_permissions?.checks?.wp_config && (
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <span className="text-sm text-slate-600">wp-config.php</span>
                  <HelpTooltip content="Contains database credentials and security keys. Should be readable by the server but not writable (644 or 600)." />
                </div>
                <div className="flex items-center gap-2">
                  <StatusIcon status={health.file_permissions.checks.wp_config.secure} />
                  <code className="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">
                    {health.file_permissions.checks.wp_config.permissions}
                  </code>
                </div>
              </div>
            )}
            {health?.file_permissions?.checks?.htaccess && (
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <span className="text-sm text-slate-600">.htaccess</span>
                  <HelpTooltip content="Controls URL routing and security headers. Should be writable for permalink changes but secured otherwise." />
                </div>
                <div className="flex items-center gap-2">
                  <code className="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">
                    {health.file_permissions.checks.htaccess.permissions}
                  </code>
                </div>
              </div>
            )}
            {health?.file_permissions?.checks?.wp_content && (
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <span className="text-sm text-slate-600">wp-content writable</span>
                  <HelpTooltip content="Must be writable for uploads, plugin/theme installations, and updates. This is normal and expected." />
                </div>
                <StatusIcon status={health.file_permissions.checks.wp_content.writable} />
              </div>
            )}

            <InfoPanel variant="info" title="Understanding Permissions" collapsible defaultOpen={false}>
              <ul className="text-sm space-y-1 mt-1">
                <li><strong>644</strong> - Owner can read/write, others read only (files)</li>
                <li><strong>755</strong> - Owner full access, others read/execute (directories)</li>
                <li><strong>600</strong> - Owner only, maximum security (sensitive files)</li>
              </ul>
            </InfoPanel>
          </div>
        </Card>

        {/* Backups */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Backups
                <HelpTooltip content={healthExplanations.backup.why} />
              </span>
            }
            action={<Archive className="w-5 h-5 text-slate-400" />}
          />
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-sm text-slate-600">Backup Plugin</span>
                <HelpTooltip content="A backup plugin automatically creates copies of your site. Without one, you're risking total data loss." />
              </div>
              <span className="font-medium">
                {health?.backup?.plugin_detected || 'None detected'}
              </span>
            </div>
            {health?.backup?.last_backup && (
              <>
                <div className="flex items-center justify-between">
                  <span className="text-sm text-slate-600">Last Backup</span>
                  <span className="font-medium">{health?.backup?.last_backup}</span>
                </div>
                {health?.backup?.days_since_last !== null && (
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-slate-600">Days Since Backup</span>
                    <Badge
                      variant={
                        (health.backup.days_since_last ?? 0) <= 1
                          ? 'success'
                          : (health.backup.days_since_last ?? 0) <= 7
                          ? 'warning'
                          : 'danger'
                      }
                    >
                      {health.backup.days_since_last} days
                    </Badge>
                  </div>
                )}
              </>
            )}

            {!health?.backup?.plugin_detected && (
              <SecurityAlert severity="high" title="No Backup System Detected">
                <p>Your site has no detected backup solution. This is extremely risky:</p>
                <ul className="mt-2 text-sm space-y-1">
                  <li>• Hacks, crashes, or mistakes could mean permanent data loss</li>
                  <li>• Hosting provider backups are often insufficient</li>
                  <li>• Recovery without backups is expensive or impossible</li>
                </ul>
                <p className="mt-2 text-sm font-medium">
                  Install a backup plugin like UpdraftPlus, BackupBuddy, or BlogVault immediately.
                </p>
              </SecurityAlert>
            )}

            {health?.backup?.days_since_last !== null && health?.backup?.days_since_last !== undefined && health.backup.days_since_last > 7 && (
              <Alert variant="warning">
                Your last backup was {health.backup.days_since_last} days ago. Consider running a backup soon or checking your backup schedule.
              </Alert>
            )}

            {health?.backup?.plugin_detected && health?.backup?.days_since_last !== null && health?.backup?.days_since_last !== undefined && health.backup.days_since_last <= 1 && (
              <InfoPanel variant="success" title="Backups Healthy">
                Great job! You have a backup plugin installed and recent backups. Remember to:
                <ul className="mt-2 text-sm space-y-1">
                  <li>• Store backups off-site (cloud storage)</li>
                  <li>• Test restoring occasionally</li>
                  <li>• Backup before major changes</li>
                </ul>
              </InfoPanel>
            )}
          </div>
        </Card>

        {/* Debug Mode Warning */}
        {health?.debug_mode && (
          <Card className="lg:col-span-2">
            <SecurityAlert severity="medium" title="Debug Mode Enabled">
              <p>
                WordPress debug mode (WP_DEBUG) is currently enabled. This is useful for development but should be disabled on production sites:
              </p>
              <ul className="mt-2 text-sm space-y-1">
                <li>• Error messages may reveal sensitive information</li>
                <li>• Performance is reduced due to extra logging</li>
                <li>• Visitors may see PHP warnings and notices</li>
              </ul>
              <p className="mt-2 text-sm">
                <strong>To disable:</strong> Set <code className="bg-slate-100 px-1 rounded">WP_DEBUG</code> to <code className="bg-slate-100 px-1 rounded">false</code> in wp-config.php
              </p>
            </SecurityAlert>
          </Card>
        )}
      </div>
    </Layout>
  );
}
