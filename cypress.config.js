import { defineConfig } from "cypress";

export default defineConfig({
  allowCypressEnv: false,
  experimentalMemoryManagement: true,
  numTestsKeptInMemory: 0,
  video: false,
  screenshotOnRunFailure: true,

  e2e: {
    setupNodeEvents(on, config) {
      on('before:browser:launch', (browser = {}, launchOptions) => {
        if (browser.family === 'chromium' && browser.name !== 'electron') {
          launchOptions.args.push('--disable-dev-shm-usage');
          launchOptions.args.push('--disable-gpu');
          launchOptions.args.push('--disable-software-rasterizer');
          launchOptions.args.push('--js-flags=--max-old-space-size=4096');
        }

        return launchOptions;
      });

      return config;
    },
  },
});
