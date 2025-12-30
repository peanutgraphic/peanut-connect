import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Badge,
  Button,
  useToast,
  HelpTooltip,
  InfoPanel,
  Alert,
  SecurityAlert,
  Modal,
  UpdatesSkeleton,
} from '@/components/common';
import { updatesApi } from '@/api';
import {
  Download,
  CheckCircle2,
  RefreshCw,
  Package,
  Palette,
  Globe,
  Loader2,
  AlertTriangle,
  Shield,
  Archive,
  ArrowUpCircle,
  Clock,
  Zap,
} from 'lucide-react';
import type { PluginUpdate, ThemeUpdate } from '@/types';

// Version comparison helper
function isMajorUpdate(current: string, next: string): boolean {
  const currentMajor = parseInt(current.split('.')[0]) || 0;
  const nextMajor = parseInt(next.split('.')[0]) || 0;
  return nextMajor > currentMajor;
}

function isMinorUpdate(current: string, next: string): boolean {
  if (isMajorUpdate(current, next)) return false;
  const currentMinor = parseInt(current.split('.')[1]) || 0;
  const nextMinor = parseInt(next.split('.')[1]) || 0;
  return nextMinor > currentMinor;
}

export default function Updates() {
  const queryClient = useQueryClient();
  const toast = useToast();
  const [updatingItems, setUpdatingItems] = useState<Set<string>>(new Set());
  const [showBackupReminder, setShowBackupReminder] = useState(true);
  const [confirmUpdate, setConfirmUpdate] = useState<{
    type: 'plugin' | 'theme' | 'core';
    slug: string;
    name: string;
    currentVersion: string;
    newVersion: string;
  } | null>(null);

  const { data: updates, isLoading, error, refetch, isFetching } = useQuery({
    queryKey: ['updates'],
    queryFn: updatesApi.get,
  });

  const updateMutation = useMutation({
    mutationFn: ({ type, slug }: { type: 'plugin' | 'theme' | 'core'; slug: string }) =>
      updatesApi.update(type, slug),
    onMutate: ({ type, slug }) => {
      const key = `${type}-${slug}`;
      setUpdatingItems((prev) => new Set([...prev, key]));
    },
    onSuccess: (result) => {
      toast.success(result.message || 'Update completed successfully');
      queryClient.invalidateQueries({ queryKey: ['updates'] });
      queryClient.invalidateQueries({ queryKey: ['health'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
    },
    onError: (err, { type, slug }) => {
      toast.error((err as Error).message || 'Update failed');
      const key = `${type}-${slug}`;
      setUpdatingItems((prev) => {
        const next = new Set(prev);
        next.delete(key);
        return next;
      });
    },
    onSettled: (_, __, { type, slug }) => {
      const key = `${type}-${slug}`;
      setUpdatingItems((prev) => {
        const next = new Set(prev);
        next.delete(key);
        return next;
      });
    },
  });

  const handleUpdate = (type: 'plugin' | 'theme' | 'core', slug: string) => {
    setConfirmUpdate(null);
    updateMutation.mutate({ type, slug });
  };

  const requestUpdate = (
    type: 'plugin' | 'theme' | 'core',
    slug: string,
    name: string,
    currentVersion: string,
    newVersion: string
  ) => {
    // For major updates or core, show confirmation
    if (type === 'core' || isMajorUpdate(currentVersion, newVersion)) {
      setConfirmUpdate({ type, slug, name, currentVersion, newVersion });
    } else {
      handleUpdate(type, slug);
    }
  };

  const isUpdating = (type: string, slug: string) => {
    return updatingItems.has(`${type}-${slug}`);
  };

  const totalUpdates =
    (updates?.plugins.length || 0) +
    (updates?.themes.length || 0) +
    (updates?.core ? 1 : 0);

  const hasAnyMajorUpdate = () => {
    if (updates?.core) return true;
    for (const plugin of updates?.plugins || []) {
      if (isMajorUpdate(plugin.version, plugin.new_version)) return true;
    }
    for (const theme of updates?.themes || []) {
      if (isMajorUpdate(theme.version, theme.new_version)) return true;
    }
    return false;
  };

  if (isLoading) {
    return (
      <Layout title="Updates" description="Available plugin, theme, and core updates">
        <UpdatesSkeleton />
      </Layout>
    );
  }

  if (error) {
    return (
      <Layout title="Updates" description="Available plugin, theme, and core updates">
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
    <Layout title="Updates" description="Available plugin, theme, and core updates">
      {/* Update Confirmation Modal */}
      <Modal
        isOpen={!!confirmUpdate}
        onClose={() => setConfirmUpdate(null)}
        title={`Update ${confirmUpdate?.name}?`}
        size="md"
      >
        {confirmUpdate && (
          <div className="space-y-4">
            <Alert variant="warning">
              <p>
                <strong>{confirmUpdate.type === 'core' ? 'WordPress Core' : 'Major'} Update</strong>
              </p>
              <p className="mt-1">
                You're updating from <code className="bg-amber-100 px-1 rounded">{confirmUpdate.currentVersion}</code> to{' '}
                <code className="bg-amber-100 px-1 rounded">{confirmUpdate.newVersion}</code>
              </p>
            </Alert>

            <InfoPanel variant="warning" title="Before You Update">
              <ul className="space-y-2 mt-2">
                <li className="flex items-start gap-2">
                  <Archive className="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" />
                  <span><strong>Create a backup</strong> of your site before updating</span>
                </li>
                <li className="flex items-start gap-2">
                  <Clock className="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" />
                  <span>Updates may take a few minutes. <strong>Don't close this page</strong></span>
                </li>
                {confirmUpdate.type === 'core' && (
                  <li className="flex items-start gap-2">
                    <AlertTriangle className="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" />
                    <span>Core updates affect your entire site. <strong>Test on staging first</strong> if possible</span>
                  </li>
                )}
                {isMajorUpdate(confirmUpdate.currentVersion, confirmUpdate.newVersion) && (
                  <li className="flex items-start gap-2">
                    <Zap className="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" />
                    <span>Major version updates may include <strong>breaking changes</strong></span>
                  </li>
                )}
              </ul>
            </InfoPanel>

            <div className="flex justify-end gap-3 pt-2">
              <Button variant="outline" onClick={() => setConfirmUpdate(null)}>
                Cancel
              </Button>
              <Button
                variant="primary"
                onClick={() => handleUpdate(confirmUpdate.type, confirmUpdate.slug)}
                loading={isUpdating(confirmUpdate.type, confirmUpdate.slug)}
              >
                <Download className="w-4 h-4 mr-2" />
                Update Now
              </Button>
            </div>
          </div>
        )}
      </Modal>

      {/* Header with actions */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          {totalUpdates > 0 ? (
            <Badge variant="warning">{totalUpdates} update{totalUpdates > 1 ? 's' : ''} available</Badge>
          ) : (
            <Badge variant="success">All up to date</Badge>
          )}
          <HelpTooltip content="Updates include security patches, bug fixes, and new features. Regular updates are essential for site security." />
        </div>
        <Button onClick={() => refetch()} loading={isFetching} variant="outline">
          <RefreshCw className="w-4 h-4 mr-2" />
          Check for Updates
        </Button>
      </div>

      {/* Backup Reminder */}
      {totalUpdates > 0 && showBackupReminder && (
        <SecurityAlert severity="medium" title="Backup Before Updating" className="mb-6">
          <div className="flex items-start justify-between gap-4">
            <div>
              <p>
                Always create a backup before updating plugins, themes, or WordPress core.
                Updates can occasionally cause compatibility issues.
              </p>
              <ul className="mt-2 text-sm space-y-1">
                <li className="flex items-center gap-2">
                  <CheckCircle2 className="w-3.5 h-3.5 text-amber-600" />
                  Backup your database and files
                </li>
                <li className="flex items-center gap-2">
                  <CheckCircle2 className="w-3.5 h-3.5 text-amber-600" />
                  Know how to restore if something goes wrong
                </li>
                <li className="flex items-center gap-2">
                  <CheckCircle2 className="w-3.5 h-3.5 text-amber-600" />
                  Test updates on a staging site when possible
                </li>
              </ul>
            </div>
            <Button
              variant="outline"
              size="sm"
              onClick={() => setShowBackupReminder(false)}
            >
              Dismiss
            </Button>
          </div>
        </SecurityAlert>
      )}

      {/* Major Update Warning */}
      {hasAnyMajorUpdate() && (
        <Alert variant="warning" title="Major Updates Available" className="mb-6">
          <p>
            Some updates include major version changes that may introduce breaking changes or require compatibility testing.
            These updates are marked with a special indicator. Consider testing on a staging environment first.
          </p>
        </Alert>
      )}

      {/* All Up to Date Message */}
      {totalUpdates === 0 && (
        <Card>
          <div className="text-center py-12">
            <CheckCircle2 className="w-16 h-16 text-green-500 mx-auto mb-4" />
            <h3 className="text-xl font-semibold text-slate-900 mb-2">
              Everything is up to date!
            </h3>
            <p className="text-slate-500 mb-4">
              Your WordPress core, plugins, and themes are all running the latest versions.
            </p>
            <InfoPanel variant="success" title="Best Practices" className="max-w-md mx-auto mt-6">
              <ul className="text-sm space-y-1 mt-1">
                <li>• Check for updates regularly (at least weekly)</li>
                <li>• Enable auto-updates for minor security releases</li>
                <li>• Subscribe to security advisories for your plugins</li>
              </ul>
            </InfoPanel>
          </div>
        </Card>
      )}

      {/* Core Updates */}
      {updates?.core && (
        <Card className="mb-6">
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                WordPress Core
                <Badge variant="danger" size="sm">Priority</Badge>
                <HelpTooltip content="WordPress core updates are critical for security. They often patch vulnerabilities that are actively being exploited." />
              </span>
            }
            action={
              <div className="flex items-center gap-3">
                <Badge variant="primary">
                  {updates.core.current_version} → {updates.core.new_version}
                </Badge>
                <Button
                  size="sm"
                  onClick={() => requestUpdate('core', 'wordpress', 'WordPress', updates.core!.current_version, updates.core!.new_version)}
                  loading={isUpdating('core', 'wordpress')}
                  icon={<Download className="w-4 h-4" />}
                >
                  Update
                </Button>
              </div>
            }
          />
          <div className="flex items-start gap-4">
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <Globe className="w-6 h-6 text-blue-600" />
            </div>
            <div className="flex-1">
              <div className="flex items-center gap-2 mb-2">
                <p className="font-medium text-slate-900">WordPress</p>
                {isMajorUpdate(updates.core.current_version, updates.core.new_version) && (
                  <Badge variant="warning" size="sm">Major Update</Badge>
                )}
              </div>
              <p className="text-sm text-slate-600 mb-3">
                Update from {updates.core.current_version} to {updates.core.new_version}
              </p>
              <InfoPanel variant="warning" title="Important" collapsible defaultOpen={false}>
                <p className="text-sm">Core updates can affect your entire site:</p>
                <ul className="mt-2 text-sm space-y-1">
                  <li>• <strong>Always backup first</strong> - database and files</li>
                  <li>• Some plugins may need compatibility updates</li>
                  <li>• Check the WordPress release notes for changes</li>
                  <li>• Your site may be briefly unavailable during update</li>
                </ul>
              </InfoPanel>
            </div>
          </div>
        </Card>
      )}

      {/* Plugin Updates */}
      {updates && updates.plugins.length > 0 && (
        <Card className="mb-6">
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Plugins
                <HelpTooltip content="Plugin updates fix bugs, add features, and patch security vulnerabilities. Outdated plugins are the #1 cause of WordPress hacks." />
              </span>
            }
            description={`${updates.plugins.length} update${updates.plugins.length > 1 ? 's' : ''} available`}
          />
          <div className="space-y-4">
            {updates.plugins.map((plugin: PluginUpdate) => {
              const major = isMajorUpdate(plugin.version, plugin.new_version);
              const minor = isMinorUpdate(plugin.version, plugin.new_version);

              return (
                <div
                  key={plugin.slug}
                  className={`flex items-center justify-between p-4 rounded-lg border ${
                    major ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <div className={`w-10 h-10 ${major ? 'bg-amber-100' : 'bg-purple-100'} rounded-lg flex items-center justify-center`}>
                      <Package className={`w-5 h-5 ${major ? 'text-amber-600' : 'text-purple-600'}`} />
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <p className="font-medium text-slate-900">{plugin.name}</p>
                        {major && (
                          <span className="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-100 px-2 py-0.5 rounded">
                            <ArrowUpCircle className="w-3 h-3" />
                            Major
                          </span>
                        )}
                        {minor && !major && (
                          <span className="inline-flex items-center text-xs font-medium text-blue-700 bg-blue-100 px-2 py-0.5 rounded">
                            Feature
                          </span>
                        )}
                      </div>
                      <div className="flex items-center gap-2 text-sm text-slate-500">
                        <span>{plugin.version}</span>
                        <span>→</span>
                        <span className="font-medium text-slate-700">{plugin.new_version}</span>
                      </div>
                    </div>
                  </div>
                  <Button
                    size="sm"
                    variant={major ? 'primary' : 'outline'}
                    onClick={() => requestUpdate('plugin', plugin.slug, plugin.name, plugin.version, plugin.new_version)}
                    disabled={isUpdating('plugin', plugin.slug)}
                  >
                    {isUpdating('plugin', plugin.slug) ? (
                      <>
                        <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        Updating...
                      </>
                    ) : (
                      <>
                        <Download className="w-4 h-4 mr-2" />
                        Update
                      </>
                    )}
                  </Button>
                </div>
              );
            })}

            {updates.plugins.length > 3 && (
              <InfoPanel variant="tip" title="Update Tip" collapsible defaultOpen={false}>
                <p>When updating multiple plugins:</p>
                <ul className="mt-2 text-sm space-y-1">
                  <li>• Update one at a time and test between updates</li>
                  <li>• Security patches (patch versions like 1.2.3 → 1.2.4) are usually safe</li>
                  <li>• Major updates (1.x → 2.x) may have breaking changes</li>
                  <li>• Check plugin changelogs for important notes</li>
                </ul>
              </InfoPanel>
            )}
          </div>
        </Card>
      )}

      {/* Theme Updates */}
      {updates && updates.themes.length > 0 && (
        <Card>
          <CardHeader
            title={
              <span className="flex items-center gap-2">
                Themes
                <HelpTooltip content="Theme updates may include design changes, bug fixes, and security patches. Always backup before updating your active theme." />
              </span>
            }
            description={`${updates.themes.length} update${updates.themes.length > 1 ? 's' : ''} available`}
          />
          <div className="space-y-4">
            {updates.themes.map((theme: ThemeUpdate) => {
              const major = isMajorUpdate(theme.version, theme.new_version);

              return (
                <div
                  key={theme.slug}
                  className={`flex items-center justify-between p-4 rounded-lg border ${
                    major ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <div className={`w-10 h-10 ${major ? 'bg-amber-100' : 'bg-green-100'} rounded-lg flex items-center justify-center`}>
                      <Palette className={`w-5 h-5 ${major ? 'text-amber-600' : 'text-green-600'}`} />
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <p className="font-medium text-slate-900">{theme.name}</p>
                        {major && (
                          <span className="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-100 px-2 py-0.5 rounded">
                            <ArrowUpCircle className="w-3 h-3" />
                            Major
                          </span>
                        )}
                      </div>
                      <div className="flex items-center gap-2 text-sm text-slate-500">
                        <span>{theme.version}</span>
                        <span>→</span>
                        <span className="font-medium text-slate-700">{theme.new_version}</span>
                      </div>
                    </div>
                  </div>
                  <Button
                    size="sm"
                    variant={major ? 'primary' : 'outline'}
                    onClick={() => requestUpdate('theme', theme.slug, theme.name, theme.version, theme.new_version)}
                    disabled={isUpdating('theme', theme.slug)}
                  >
                    {isUpdating('theme', theme.slug) ? (
                      <>
                        <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                        Updating...
                      </>
                    ) : (
                      <>
                        <Download className="w-4 h-4 mr-2" />
                        Update
                      </>
                    )}
                  </Button>
                </div>
              );
            })}

            <InfoPanel variant="info" title="Theme Updates" collapsible defaultOpen={false}>
              <ul className="text-sm space-y-1 mt-1">
                <li>• <strong>Active theme:</strong> Test appearance after updating</li>
                <li>• <strong>Child themes:</strong> Parent theme updates are usually safe</li>
                <li>• <strong>Customizations:</strong> Direct edits may be lost - use child themes</li>
                <li>• <strong>Inactive themes:</strong> Delete themes you don't use for security</li>
              </ul>
            </InfoPanel>
          </div>
        </Card>
      )}

      {/* Update Best Practices */}
      {totalUpdates > 0 && (
        <Card className="mt-6">
          <CardHeader title="Update Best Practices" />
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="p-4 bg-slate-50 rounded-lg">
              <div className="flex items-center gap-2 mb-2">
                <Archive className="w-5 h-5 text-slate-600" />
                <h4 className="font-medium text-slate-900">Always Backup</h4>
              </div>
              <p className="text-sm text-slate-600">
                Create a full backup before any updates. If something breaks, you can restore quickly.
              </p>
            </div>
            <div className="p-4 bg-slate-50 rounded-lg">
              <div className="flex items-center gap-2 mb-2">
                <Clock className="w-5 h-5 text-slate-600" />
                <h4 className="font-medium text-slate-900">Update Regularly</h4>
              </div>
              <p className="text-sm text-slate-600">
                Don't let updates pile up. Regular small updates are safer than occasional big ones.
              </p>
            </div>
            <div className="p-4 bg-slate-50 rounded-lg">
              <div className="flex items-center gap-2 mb-2">
                <Shield className="w-5 h-5 text-slate-600" />
                <h4 className="font-medium text-slate-900">Security First</h4>
              </div>
              <p className="text-sm text-slate-600">
                Security updates should be applied immediately. They patch known vulnerabilities.
              </p>
            </div>
          </div>
        </Card>
      )}
    </Layout>
  );
}
