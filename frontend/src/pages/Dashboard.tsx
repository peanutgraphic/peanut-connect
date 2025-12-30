import { useQuery } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  StatCard,
  Badge,
  Button,
  InfoPanel,
  FeatureCard,
  Alert,
  Recommendation,
  HelpTooltip,
  DashboardSkeleton,
} from '@/components/common';
import { dashboardApi, settingsApi } from '@/api';
import {
  Link2,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  Download,
  Activity,
  RefreshCw,
  Shield,
  Eye,
  Zap,
  Settings,
  ArrowRight,
  Sparkles,
} from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { Link } from 'react-router-dom';

export default function Dashboard() {
  const { data: dashboard, isLoading, error, refetch, isFetching } = useQuery({
    queryKey: ['dashboard'],
    queryFn: dashboardApi.get,
  });

  const { data: settings } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsApi.get,
  });

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'healthy':
        return <CheckCircle2 className="w-5 h-5 text-green-500" />;
      case 'warning':
        return <AlertTriangle className="w-5 h-5 text-amber-500" />;
      case 'critical':
        return <XCircle className="w-5 h-5 text-red-500" />;
      default:
        return null;
    }
  };

  const getStatusVariant = (status: string): 'success' | 'warning' | 'danger' => {
    switch (status) {
      case 'healthy':
        return 'success';
      case 'warning':
        return 'warning';
      case 'critical':
        return 'danger';
      default:
        return 'warning';
    }
  };

  if (isLoading) {
    return (
      <Layout title="Dashboard" description="Site connection overview">
        <DashboardSkeleton />
      </Layout>
    );
  }

  if (error) {
    return (
      <Layout title="Dashboard" description="Site connection overview">
        <Card>
          <div className="text-center py-8">
            <XCircle className="w-12 h-12 text-red-500 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-slate-900 mb-2">
              Failed to load dashboard
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

  const totalUpdates = (dashboard?.updates.plugins || 0) +
                       (dashboard?.updates.themes || 0) +
                       (dashboard?.updates.core ? 1 : 0);

  const isConnected = dashboard?.connection.connected;
  const showWelcome = !isConnected && !settings?.connection?.site_key;

  return (
    <Layout title="Dashboard" description="Site connection overview">
      {/* Welcome Panel for New Users */}
      {showWelcome && (
        <InfoPanel
          variant="guide"
          title="Welcome to Peanut Connect!"
          className="mb-6"
        >
          <p className="mb-3">
            Peanut Connect allows you to remotely monitor and manage this WordPress site from a central manager dashboard.
            Here's what you can do:
          </p>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
            <div className="flex items-start gap-2">
              <Eye className="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-medium text-purple-800 text-sm">Monitor Health</p>
                <p className="text-xs text-purple-600">Track WordPress, PHP, SSL, and more</p>
              </div>
            </div>
            <div className="flex items-start gap-2">
              <Download className="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-medium text-purple-800 text-sm">Manage Updates</p>
                <p className="text-xs text-purple-600">Update plugins, themes, and core remotely</p>
              </div>
            </div>
            <div className="flex items-start gap-2">
              <Shield className="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-medium text-purple-800 text-sm">Stay Secure</p>
                <p className="text-xs text-purple-600">Get security checks and recommendations</p>
              </div>
            </div>
          </div>
          <div className="mt-4">
            <Link
              to="/settings"
              className="inline-flex items-center gap-1 text-sm font-medium text-purple-700 hover:text-purple-900"
            >
              Get started by generating a site key
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </InfoPanel>
      )}

      {/* Quick Actions Bar */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-2">
          <span className="text-sm text-slate-500">
            Last updated: {dashboard?.connection.last_sync
              ? formatDistanceToNow(new Date(dashboard.connection.last_sync), { addSuffix: true })
              : 'Never'}
          </span>
        </div>
        <Button
          onClick={() => refetch()}
          loading={isFetching}
          variant="outline"
          size="sm"
        >
          <RefreshCw className="w-4 h-4 mr-2" />
          Refresh
        </Button>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          title={
            <span className="flex items-center gap-1.5">
              Connection Status
              <HelpTooltip content="Shows whether this site is connected to a Peanut Manager. A connected site can be monitored and managed remotely." />
            </span>
          }
          value={dashboard?.connection.connected ? 'Connected' : 'Disconnected'}
          icon={<Link2 className="w-5 h-5" />}
        />
        <StatCard
          title={
            <span className="flex items-center gap-1.5">
              Health Status
              <HelpTooltip content="Overall health assessment based on WordPress version, PHP version, SSL status, pending updates, and security checks." />
            </span>
          }
          value={dashboard?.health_summary.status === 'healthy' ? 'Healthy' :
                 dashboard?.health_summary.status === 'warning' ? 'Warning' : 'Critical'}
          icon={<Activity className="w-5 h-5" />}
        />
        <StatCard
          title={
            <span className="flex items-center gap-1.5">
              Available Updates
              <HelpTooltip content="Total number of available updates for plugins, themes, and WordPress core. Keep your site updated for security and performance." />
            </span>
          }
          value={totalUpdates}
          icon={<Download className="w-5 h-5" />}
        />
        <StatCard
          title={
            <span className="flex items-center gap-1.5">
              Peanut Suite
              <HelpTooltip content="Peanut Suite provides marketing tools including UTM tracking, link management, contacts, and analytics. When installed, data syncs with your manager." />
            </span>
          }
          value={dashboard?.peanut_suite?.installed ? 'Active' : 'Not Installed'}
          icon={<Sparkles className="w-5 h-5" />}
        />
      </div>

      {/* Critical Health Alert */}
      {dashboard?.health_summary.status === 'critical' && (
        <Alert variant="danger" title="Critical Issues Detected" className="mb-6">
          <p>Your site has critical issues that need immediate attention. These may affect security, performance, or functionality.</p>
          <Link
            to="/health"
            className="inline-flex items-center gap-1 mt-2 text-sm font-medium text-red-700 hover:text-red-900"
          >
            View health details
            <ArrowRight className="w-4 h-4" />
          </Link>
        </Alert>
      )}

      {/* Updates Available Alert */}
      {totalUpdates > 0 && (
        <Recommendation
          title={`${totalUpdates} Update${totalUpdates > 1 ? 's' : ''} Available`}
          action={{
            label: 'View Updates',
            onClick: () => window.location.hash = '#/updates',
          }}
        >
          Keeping your WordPress site updated is crucial for security and performance.
          {dashboard?.updates.core && ' A WordPress core update is available - this should be prioritized.'}
        </Recommendation>
      )}

      {/* Connection Card */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 mt-6">
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Connection Status
                <HelpTooltip content="Connection to your Peanut Manager site. When connected, you can monitor and manage this site remotely from your manager dashboard." />
              </span>
            }
            action={
              dashboard?.connection.connected ? (
                <Badge variant="success">Connected</Badge>
              ) : (
                <Badge variant="danger">Disconnected</Badge>
              )
            }
          />
          {dashboard?.connection.connected ? (
            <div className="space-y-4">
              <div className="flex items-center gap-2 text-green-600">
                <CheckCircle2 className="w-5 h-5" />
                <span className="font-medium">Connected to Manager</span>
              </div>
              {dashboard.connection.manager_url && (
                <div className="p-3 bg-slate-50 rounded-lg">
                  <p className="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Manager URL</p>
                  <p className="text-sm text-slate-700 font-mono break-all">
                    {dashboard.connection.manager_url}
                  </p>
                </div>
              )}
              {dashboard.connection.last_sync && (
                <p className="text-sm text-slate-500">
                  Last sync:{' '}
                  {formatDistanceToNow(new Date(dashboard.connection.last_sync), {
                    addSuffix: true,
                  })}
                </p>
              )}
              <InfoPanel variant="success" title="What this means" collapsible defaultOpen={false}>
                <ul className="text-sm space-y-1 mt-1">
                  <li>• Your site health is being monitored</li>
                  <li>• Updates can be performed remotely</li>
                  <li>• Security alerts will be sent to your manager</li>
                  <li>• Analytics data is synced (if Peanut Suite is active)</li>
                </ul>
              </InfoPanel>
            </div>
          ) : (
            <div className="space-y-4">
              <div className="flex items-center gap-2 text-amber-600">
                <AlertTriangle className="w-5 h-5" />
                <span className="font-medium">Not connected to any manager site</span>
              </div>
              <div className="p-4 bg-slate-50 rounded-lg border border-slate-200">
                <h4 className="font-medium text-slate-900 mb-2">How to Connect</h4>
                <ol className="text-sm text-slate-600 space-y-2">
                  <li className="flex items-start gap-2">
                    <span className="w-5 h-5 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">1</span>
                    Go to <Link to="/settings" className="text-primary-600 hover:underline">Settings</Link> and generate a site key
                  </li>
                  <li className="flex items-start gap-2">
                    <span className="w-5 h-5 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">2</span>
                    Copy the site key and your site URL
                  </li>
                  <li className="flex items-start gap-2">
                    <span className="w-5 h-5 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">3</span>
                    Add this site in your Peanut Manager dashboard
                  </li>
                </ol>
              </div>
              <Link to="/settings">
                <Button variant="primary" size="sm">
                  <Settings className="w-4 h-4 mr-2" />
                  Go to Settings
                </Button>
              </Link>
            </div>
          )}
        </Card>

        {/* Health Summary Card */}
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Health Summary
                <HelpTooltip content="Comprehensive health check of your WordPress installation including server configuration, security settings, and update status." />
              </span>
            }
            action={
              dashboard?.health_summary && (
                <Badge variant={getStatusVariant(dashboard.health_summary.status)}>
                  {dashboard.health_summary.status.charAt(0).toUpperCase() +
                   dashboard.health_summary.status.slice(1)}
                </Badge>
              )
            }
          />
          <div className="space-y-4">
            {dashboard?.health_summary && (
              <>
                <div className="flex items-center gap-2">
                  {getStatusIcon(dashboard.health_summary.status)}
                  <span className="font-medium text-slate-900">
                    {dashboard.health_summary.status === 'healthy'
                      ? 'All systems operational'
                      : dashboard.health_summary.status === 'warning'
                      ? 'Some issues detected'
                      : 'Critical issues found'}
                  </span>
                </div>

                {dashboard.health_summary.issues.length > 0 ? (
                  <div className="space-y-2">
                    <p className="text-xs font-medium text-slate-500 uppercase tracking-wide">Issues Found</p>
                    <ul className="space-y-2">
                      {dashboard.health_summary.issues.slice(0, 4).map((issue, i) => (
                        <li key={i} className="flex items-start gap-2 p-2 bg-amber-50 rounded-lg border border-amber-100">
                          <AlertTriangle className="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" />
                          <span className="text-sm text-amber-800">{issue}</span>
                        </li>
                      ))}
                      {dashboard.health_summary.issues.length > 4 && (
                        <li className="text-sm text-slate-500 pl-6">
                          +{dashboard.health_summary.issues.length - 4} more issues
                        </li>
                      )}
                    </ul>
                  </div>
                ) : (
                  <div className="p-4 bg-green-50 rounded-lg border border-green-100">
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-5 h-5 text-green-500" />
                      <p className="text-sm text-green-800">
                        No issues detected. Your site is healthy!
                      </p>
                    </div>
                  </div>
                )}

                <Link
                  to="/health"
                  className="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-800"
                >
                  View detailed health report
                  <ArrowRight className="w-4 h-4" />
                </Link>
              </>
            )}
          </div>
        </Card>
      </div>

      {/* Updates Summary */}
      {totalUpdates > 0 && (
        <Card className="mb-6">
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Available Updates
                <HelpTooltip content="Updates improve security, fix bugs, and add new features. Always backup before updating, and test updates on a staging site when possible." />
              </span>
            }
            action={
              <Link to="/updates">
                <Button variant="outline" size="sm">
                  View All
                  <ArrowRight className="w-4 h-4 ml-1" />
                </Button>
              </Link>
            }
          />
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-lg">
              <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <Download className="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-slate-900">
                  {dashboard?.updates.plugins || 0}
                </p>
                <p className="text-sm text-slate-500">Plugin updates</p>
              </div>
            </div>
            <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-lg">
              <div className="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <Download className="w-5 h-5 text-purple-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-slate-900">
                  {dashboard?.updates.themes || 0}
                </p>
                <p className="text-sm text-slate-500">Theme updates</p>
              </div>
            </div>
            <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-lg">
              <div className={`w-10 h-10 ${dashboard?.updates.core ? 'bg-amber-100' : 'bg-green-100'} rounded-lg flex items-center justify-center`}>
                {dashboard?.updates.core ? (
                  <Download className="w-5 h-5 text-amber-600" />
                ) : (
                  <CheckCircle2 className="w-5 h-5 text-green-600" />
                )}
              </div>
              <div>
                <p className="text-2xl font-bold text-slate-900">
                  {dashboard?.updates.core ? 'Available' : 'Current'}
                </p>
                <p className="text-sm text-slate-500">WordPress core</p>
              </div>
            </div>
          </div>

          {dashboard?.updates.core && (
            <Alert variant="warning" className="mt-4">
              <strong>WordPress Core Update Available:</strong> Core updates often include critical security fixes.
              We recommend backing up your site before updating.
            </Alert>
          )}
        </Card>
      )}

      {/* Peanut Suite Integration */}
      {settings?.peanut_suite ? (
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Peanut Suite Integration
                <HelpTooltip content="Peanut Suite is a marketing toolkit that includes UTM tracking, link shortening, contact management, popups, and analytics. Data syncs with your manager." />
              </span>
            }
            action={<Badge variant="success">Active</Badge>}
          />
          <div className="flex items-start gap-4">
            <div className="p-3 bg-gradient-to-br from-amber-100 to-orange-100 rounded-lg">
              <Sparkles className="w-6 h-6 text-amber-600" />
            </div>
            <div className="flex-1">
              <p className="font-medium text-slate-900">
                Peanut Suite v{settings.peanut_suite.version}
              </p>
              <p className="text-sm text-slate-600 mt-1">
                Analytics and marketing data will sync with your manager site when enabled in permissions.
              </p>
              {settings.peanut_suite.modules.length > 0 && (
                <div className="mt-3">
                  <p className="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Active Modules</p>
                  <div className="flex flex-wrap gap-2">
                    {settings.peanut_suite.modules.map((module) => (
                      <Badge key={module} variant="primary" size="sm">
                        {module}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        </Card>
      ) : (
        <Card>
          <CardHeader title="Peanut Suite" />
          <div className="text-center py-6">
            <div className="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Sparkles className="w-8 h-8 text-slate-400" />
            </div>
            <h3 className="font-medium text-slate-900 mb-2">Peanut Suite Not Installed</h3>
            <p className="text-sm text-slate-600 max-w-md mx-auto">
              Install Peanut Suite to unlock marketing features including UTM tracking,
              link management, contact forms, and analytics that sync with your manager.
            </p>
          </div>
        </Card>
      )}

      {/* Quick Feature Overview - Only show for new/disconnected users */}
      {!isConnected && (
        <div className="mt-8">
          <h2 className="text-lg font-semibold text-slate-900 mb-4">What Peanut Connect Can Do</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <FeatureCard
              icon={<Eye className="w-5 h-5" />}
              title="Remote Monitoring"
              description="Monitor your site's health, performance, and security status from anywhere."
              useCases={[
                'Check if WordPress needs updates',
                'Monitor SSL certificate expiry',
                'Track plugin compatibility issues',
              ]}
            />
            <FeatureCard
              icon={<Zap className="w-5 h-5" />}
              title="One-Click Updates"
              description="Update plugins, themes, and WordPress core with a single click from your manager."
              useCases={[
                'Batch update multiple plugins',
                'Schedule maintenance windows',
                'Roll out updates across sites',
              ]}
            />
            <FeatureCard
              icon={<Shield className="w-5 h-5" />}
              title="Security Checks"
              description="Regular security scans and recommendations to keep your site protected."
              useCases={[
                'Check file permissions',
                'Monitor debug mode status',
                'Verify backup configurations',
              ]}
            />
          </div>
        </div>
      )}
    </Layout>
  );
}
