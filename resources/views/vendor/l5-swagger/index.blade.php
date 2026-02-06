<!DOCTYPE html>
<html>
  <head>
    <title>{{ config('l5-swagger.defaults.ui.title', 'Population API Documentation') }}</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.15.5/swagger-ui.min.css">
    <link rel="icon" type="image/png" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.15.5/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.15.5/favicon-16x16.png" sizes="16x16" />
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      
      html {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
        background-color: #0f0f0f;
      }
      
      *, *:before, *:after {
        box-sizing: inherit;
      }
      
      body {
        margin: 0;
        background: #0f0f0f !important;
        color: #e8e8e8 !important;
        font-family: sans-serif;
      }

      /* Scroll bar styling */
      ::-webkit-scrollbar {
        width: 12px;
        height: 12px;
      }

      ::-webkit-scrollbar-track {
        background: #1a1a1a;
      }

      ::-webkit-scrollbar-thumb {
        background: #404040;
        border-radius: 6px;
      }

      ::-webkit-scrollbar-thumb:hover {
        background: #555;
      }

      /* Swagger UI Main */
      .swagger-ui {
        background-color: #0f0f0f !important;
        color: #e8e8e8 !important;
      }

      .swagger-ui * {
        color: inherit;
      }

      .swagger-ui .wrapper {
        background-color: #0f0f0f !important;
      }

      /* Topbar */
      .swagger-ui .topbar {
        background: linear-gradient(to right, #1a1a1a, #0a0a0a) !important;
        border-bottom: 3px solid #2196f3 !important;
      }

      .swagger-ui .topbar ul {
        background-color: transparent !important;
      }

      .swagger-ui .topbar ul li {
        border-right-color: #333 !important;
      }

      .swagger-ui .topbar-title {
        color: #fff !important;
        font-weight: 700;
        font-size: 1.5em;
      }

      .swagger-ui .topbar a {
        color: #64b5f6 !important;
      }

      .swagger-ui .topbar a:hover {
        color: #90caf9 !important;
      }

      /* Info box */
      .swagger-ui .info {
        background: linear-gradient(135deg, #1e1e1e 0%, #242424 100%) !important;
        border: 1px solid #333 !important;
        border-radius: 6px !important;
        padding: 25px !important;
        margin: 25px 0 !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
      }

      .swagger-ui .info .title {
        color: #fff !important;
        font-weight: 600;
      }

      .swagger-ui .info .base-url {
        color: #90caf9 !important;
      }

      .swagger-ui .info .description {
        color: #b8b8b8 !important;
      }

      /* Scheme container */
      .swagger-ui .scheme-container {
        background: #1a1a1a !important;
        border: 1px solid #333 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      }

      /* Operation blocks */
      .swagger-ui .opblock {
        background-color: #1a1a1a !important;
        border: 1px solid #333 !important;
        margin: 15px 0 !important;
        border-radius: 6px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      }

      .swagger-ui .opblock.opblock-get {
        background-color: rgba(74, 144, 226, 0.05) !important;
        border-left: 5px solid #4a90e2 !important;
      }

      .swagger-ui .opblock.opblock-post {
        background-color: rgba(33, 150, 243, 0.05) !important;
        border-left: 5px solid #2196f3 !important;
      }

      .swagger-ui .opblock.opblock-put {
        background-color: rgba(255, 193, 7, 0.05) !important;
        border-left: 5px solid #ffc107 !important;
      }

      .swagger-ui .opblock.opblock-delete {
        background-color: rgba(211, 47, 47, 0.05) !important;
        border-left: 5px solid #d32f2f !important;
      }

      .swagger-ui .opblock.opblock-patch {
        background-color: rgba(233, 30, 99, 0.05) !important;
        border-left: 5px solid #e91e63 !important;
      }

      .swagger-ui .opblock .opblock-summary {
        border-color: #333 !important;
        background-color: transparent !important;
      }

      .swagger-ui .opblock .opblock-summary-method {
        background-color: #333 !important;
        color: #fff !important;
        margin-right: 15px !important;
        font-size: 12px !important;
        font-weight: bold !important;
        line-height: 14px !important;
        padding: 6px 15px !important;
        border-radius: 3px !important;
        min-width: 60px;
        text-align: center;
      }

      .swagger-ui .opblock.opblock-get .opblock-summary-method {
        background-color: #4a90e2 !important;
        color: #fff !important;
      }

      .swagger-ui .opblock.opblock-post .opblock-summary-method {
        background-color: #2196f3 !important;
        color: #fff !important;
      }

      .swagger-ui .opblock.opblock-put .opblock-summary-method {
        background-color: #ffc107 !important;
        color: #000 !important;
      }

      .swagger-ui .opblock.opblock-delete .opblock-summary-method {
        background-color: #d32f2f !important;
        color: #fff !important;
      }

      .swagger-ui .opblock.opblock-patch .opblock-summary-method {
        background-color: #e91e63 !important;
        color: #fff !important;
      }

      .swagger-ui .opblock .opblock-summary-path {
        color: #e8e8e8 !important;
        font-size: 16px;
        font-weight: 500;
      }

      .swagger-ui .opblock .opblock-summary-description {
        color: #b8b8b8 !important;
        font-size: 13px;
      }

      .swagger-ui .opblock .opblock-section-header {
        background: #1a1a1a !important;
        border-bottom: 1px solid #333 !important;
      }

      .swagger-ui .opblock .opblock-section-header > label {
        color: #e8e8e8 !important;
      }

      /* Parameters */
      .swagger-ui .parameter__name {
        color: #64b5f6 !important;
        font-weight: 600;
      }

      .swagger-ui .parameter__name.required::after {
        color: #ff6b6b !important;
      }

      .swagger-ui .parameter__type {
        color: #81c784 !important;
      }

      .swagger-ui .parameter__in {
        color: #a8a8a8 !important;
      }

      .swagger-ui .parameter__description {
        color: #b8b8b8 !important;
      }

      /* Input fields */
      .swagger-ui input[type="text"],
      .swagger-ui input[type="password"],
      .swagger-ui input[type="search"],
      .swagger-ui input[type="email"],
      .swagger-ui input[type="url"],
      .swagger-ui input[type="number"],
      .swagger-ui textarea,
      .swagger-ui select {
        background-color: #1a1a1a !important;
        color: #e8e8e8 !important;
        border: 1px solid #444 !important;
        border-radius: 4px !important;
      }

      .swagger-ui input[type="text"]:focus,
      .swagger-ui input[type="password"]:focus,
      .swagger-ui input[type="search"]:focus,
      .swagger-ui input[type="email"]:focus,
      .swagger-ui input[type="url"]:focus,
      .swagger-ui input[type="number"]:focus,
      .swagger-ui textarea:focus,
      .swagger-ui select:focus {
        background-color: #242424 !important;
        border-color: #64b5f6 !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(100, 181, 246, 0.1);
      }

      /* Response section */
      .swagger-ui .response-col_description {
        color: #b8b8b8 !important;
      }

      .swagger-ui .response-col_status {
        color: #4caf50 !important;
        font-weight: 600;
      }

      .swagger-ui .response {
        background: #1a1a1a !important;
        border: 1px solid #333 !important;
        border-radius: 4px !important;
        margin: 10px 0;
      }

      /* Models */
      .swagger-ui .model {
        background-color: #1a1a1a !important;
        border: 1px solid #333 !important;
        border-radius: 4px !important;
      }

      .swagger-ui .model-toggle {
        color: #64b5f6 !important;
      }

      .swagger-ui .model-toggle::after {
        background-color: #64b5f6 !important;
      }

      .swagger-ui .model-title {
        color: #e8e8e8 !important;
      }

      /* Tabs */
      .swagger-ui .tabitem {
        color: #b8b8b8 !important;
        border-bottom: 2px solid transparent !important;
      }

      .swagger-ui .tabitem.active {
        color: #64b5f6 !important;
        border-bottom-color: #64b5f6 !important;
      }

      /* Code blocks and pre */
      .swagger-ui pre {
        background-color: #0a0a0a !important;
        color: #76ff03 !important;
        border: 1px solid #333 !important;
        border-radius: 4px !important;
      }

      .swagger-ui code {
        background-color: #1a1a1a !important;
        color: #76ff03 !important;
        padding: 2px 6px !important;
        border-radius: 3px;
      }

      .swagger-ui .markdown code {
        background-color: #1a1a1a !important;
        color: #e8e8e8 !important;
      }

      .swagger-ui .renderedMarkdown code,
      .swagger-ui .renderedMarkdown pre {
        background-color: #1a1a1a !important;
        border: 1px solid #333 !important;
      }

      .swagger-ui .microlight {
        background-color: #0a0a0a !important;
        color: #76ff03 !important;
      }

      /* Buttons */
      .swagger-ui .btn {
        background-color: #2196f3 !important;
        color: white !important;
        border: none !important;
        border-radius: 4px !important;
        padding: 8px 16px !important;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .swagger-ui .btn:hover {
        background-color: #1976d2 !important;
        box-shadow: 0 4px 8px rgba(33, 150, 243, 0.3);
      }

      .swagger-ui .btn.cancel {
        background-color: #d32f2f !important;
      }

      .swagger-ui .btn.cancel:hover {
        background-color: #b71c1c !important;
        box-shadow: 0 4px 8px rgba(211, 47, 47, 0.3);
      }

      /* Links */
      .swagger-ui a {
        color: #64b5f6 !important;
        text-decoration: none;
      }

      .swagger-ui a:hover {
        color: #90caf9 !important;
        text-decoration: underline;
      }

      /* Labels and text */
      .swagger-ui label {
        color: #e8e8e8 !important;
      }

      .swagger-ui .form-group label {
        color: #b8b8b8 !important;
      }

      /* Errors and alerts */
      .swagger-ui .error-container {
        background: rgba(211, 47, 47, 0.1) !important;
        border: 1px solid #d32f2f !important;
        color: #ff6b6b !important;
        border-radius: 4px;
      }

      .swagger-ui .errors {
        color: #ff6b6b !important;
      }

      /* Json/Xml examples */
      .swagger-ui .example {
        background-color: #0a0a0a !important;
        border: 1px solid #333 !important;
      }

      /* Additional refinements */
      .swagger-ui .btn-group {
        background-color: #1a1a1a !important;
        border: 1px solid #333 !important;
      }

      .swagger-ui .inline {
        background-color: #1a1a1a !important;
        border: 1px solid #333 !important;
      }

      /* Tables */
      .swagger-ui table {
        background-color: #1a1a1a !important;
      }

      .swagger-ui table thead {
        background-color: #0f0f0f !important;
        border-bottom: 2px solid #333 !important;
      }

      .swagger-ui table tbody tr {
        border-bottom: 1px solid #333 !important;
      }

      .swagger-ui table th {
        color: #e8e8e8 !important;
        font-weight: 600;
      }

      .swagger-ui table td {
        color: #b8b8b8 !important;
      }

      /* Swagger UI documentation */
      .swagger-ui .info-description {
        color: #b8b8b8 !important;
      }

      /* Clear any white backgrounds */
      .swagger-ui .white {
        background-color: #1a1a1a !important;
      }

      .swagger-ui .bg-white {
        background-color: #0f0f0f !important;
      }
    </style>
  </head>

  <body>
    <div id="swagger-ui"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.15.5/swagger-ui-bundle.min.js" charset="UTF-8"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.15.5/swagger-ui-standalone-preset.min.js" charset="UTF-8"></script>
    <script>
    window.onload = function() {
      window.ui = SwaggerUIBundle({
        url: "{{ asset('/docs/default.json') }}",
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        plugins: [
          SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "StandaloneLayout"
      })
    }
  </script>
  </body>
</html>
