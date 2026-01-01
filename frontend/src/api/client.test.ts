import { describe, it, expect, vi } from 'vitest';
import api, { isWordPressAdmin, getVersion } from './client';

describe('API Client', () => {
  describe('isWordPressAdmin', () => {
    it('returns true when peanutConnect is defined', () => {
      expect(isWordPressAdmin()).toBe(true);
    });

    it('returns false when peanutConnect is undefined', () => {
      const original = window.peanutConnect;
      // @ts-expect-error - Temporarily unset for testing
      window.peanutConnect = undefined;

      expect(isWordPressAdmin()).toBe(false);

      // Restore
      window.peanutConnect = original;
    });
  });

  describe('getVersion', () => {
    it('returns version from peanutConnect', () => {
      expect(getVersion()).toBe('2.1.3');
    });

    it('returns default version when peanutConnect is undefined', () => {
      const original = window.peanutConnect;
      // @ts-expect-error - Temporarily unset for testing
      window.peanutConnect = undefined;

      expect(getVersion()).toBe('1.0.0');

      // Restore
      window.peanutConnect = original;
    });
  });

  describe('axios instance', () => {
    it('has correct baseURL configured', () => {
      expect(api.defaults.baseURL).toBe('http://localhost/wp-json/peanut-connect/v1');
    });

    it('has X-WP-Nonce header configured', () => {
      expect(api.defaults.headers['X-WP-Nonce']).toBe('test-nonce-12345');
    });

    it('has Content-Type header configured', () => {
      expect(api.defaults.headers['Content-Type']).toBe('application/json');
    });
  });
});
