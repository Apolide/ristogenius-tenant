(function () {
  "use strict";

  const storageKey = "ristopilotManageTheme";
  const managePath =
    window.location.pathname === "/manage" ||
    window.location.pathname.startsWith("/manage/");

  if (!managePath) {
    return;
  }

  const html = document.documentElement;

  function applyTheme(theme) {
    const dark = theme === "dark";

    html.classList.toggle("dark", dark);
    html.classList.toggle("light", !dark);
    html.setAttribute("data-header-styles", dark ? "dark" : "light");
    html.setAttribute("data-menu-styles", dark ? "dark" : "light");
    html.style.colorScheme = dark ? "dark" : "light";

    if (document.body) {
      document.body.style.setProperty(
        "color",
        dark ? "rgb(255 255 255)" : "rgb(0 0 0)"
      );
    }

    document.querySelectorAll("[data-manage-theme-icon='light']").forEach((icon) => {
      icon.style.setProperty("display", dark ? "none" : "block", "important");
    });
    document.querySelectorAll("[data-manage-theme-icon='dark']").forEach((icon) => {
      icon.style.setProperty("display", dark ? "block" : "none", "important");
    });
    document.querySelectorAll("[data-manage-theme-toggle]").forEach((button) => {
      const label = dark ? "Attiva il tema chiaro" : "Attiva il tema scuro";

      button.setAttribute("aria-label", label);
      button.setAttribute("title", label);
    });
  }

  function storedTheme() {
    try {
      return localStorage.getItem(storageKey) === "dark" ? "dark" : "light";
    } catch (error) {
      return "light";
    }
  }

  function saveTheme(theme) {
    try {
      localStorage.setItem(storageKey, theme);
    } catch (error) {
      // The selected theme still applies to the current page if storage is unavailable.
    }
  }

  function bindThemeToggle() {
    document.querySelectorAll("[data-manage-theme-toggle]").forEach((button) => {
      if (button.dataset.manageThemeBound === "true") {
        return;
      }

      button.dataset.manageThemeBound = "true";
      button.addEventListener("click", () => {
        const theme = html.classList.contains("dark") ? "light" : "dark";

        applyTheme(theme);
        saveTheme(theme);
      });
    });

    applyTheme(html.classList.contains("dark") ? "dark" : "light");
  }

  // Apply the stored preference immediately, before the stylesheets are parsed.
  applyTheme(storedTheme());

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bindThemeToggle);
  } else {
    bindThemeToggle();
  }

  // Livewire can replace parts of the DOM without doing a full page load.
  document.addEventListener("livewire:navigated", bindThemeToggle);
})();
