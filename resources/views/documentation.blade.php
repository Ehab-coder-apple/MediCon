<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - GPS Attendance Documentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-transform: capitalize;
        }

        header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .nav-links {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .nav-links a {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .nav-links a:hover {
            background: #764ba2;
        }

        .nav-links a.active {
            background: #764ba2;
            font-weight: bold;
        }

        .content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1.8;
        }

        .content h1 {
            font-size: 2em;
            margin: 30px 0 20px 0;
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .content h2 {
            font-size: 1.6em;
            margin: 25px 0 15px 0;
            color: #764ba2;
        }

        .content h3 {
            font-size: 1.3em;
            margin: 20px 0 10px 0;
            color: #555;
        }

        .content p {
            margin: 15px 0;
            text-align: justify;
        }

        .content ul, .content ol {
            margin: 15px 0 15px 30px;
        }

        .content li {
            margin: 8px 0;
        }

        .content code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #d63384;
        }

        .content pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }

        .content pre code {
            color: #333;
            padding: 0;
            background: none;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .content table th,
        .content table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .content table th {
            background: #667eea;
            color: white;
        }

        .content table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .content blockquote {
            border-left: 4px solid #667eea;
            padding-left: 20px;
            margin: 20px 0;
            color: #666;
            font-style: italic;
        }

        .content strong {
            color: #333;
            font-weight: 600;
        }

        .content em {
            color: #666;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .back-link:hover {
            background: #764ba2;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #666;
            margin-top: 40px;
            border-top: 1px solid #ddd;
        }

        .print-button {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            transition: background 0.3s;
            margin-left: 10px;
        }

        .print-button:hover {
            background: #218838;
        }

        @media print {
            header, .nav-links, .back-link, .print-button {
                display: none;
            }
            body {
                background: white;
            }
            .content {
                box-shadow: none;
                padding: 0;
            }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 1.8em;
            }
            .content {
                padding: 20px;
            }
            .nav-links {
                flex-direction: column;
            }
            .nav-links a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📚 GPS Attendance System Documentation</h1>
            <p>{{ ucfirst(str_replace('-', ' ', $title)) }}</p>
        </header>

        <div class="nav-links">
            <a href="http://127.0.0.1:8000/docs/start-here" class="@if($title === 'start-here') active @endif">Start Here</a>
            <a href="http://127.0.0.1:8000/docs/admin-guide" class="@if($title === 'admin-guide') active @endif">Admin Guide</a>
            <a href="http://127.0.0.1:8000/docs/quick-reference" class="@if($title === 'quick-reference') active @endif">Quick Reference</a>
            <a href="http://127.0.0.1:8000/docs/troubleshooting" class="@if($title === 'troubleshooting') active @endif">Troubleshooting</a>
            <a href="http://127.0.0.1:8000/docs/training" class="@if($title === 'training') active @endif">Training</a>
            <a href="http://127.0.0.1:8000/docs/index" class="@if($title === 'index') active @endif">Index</a>
        </div>

        <div class="content">
            <a href="http://127.0.0.1:8000/admin/attendance" class="back-link">← Back to Attendance</a>
            <button class="print-button" onclick="window.print()">🖨️ Print as PDF</button>

            <pre style="white-space: pre-wrap; word-wrap: break-word; font-family: inherit; background: transparent; border: none; padding: 0;">{{ $content }}</pre>
        </div>

        <footer>
            <p>&copy; 2025 MediCon GPS Attendance System. All Rights Reserved.</p>
            <p>Documentation Version 1.0 | Last Updated: October 24, 2025</p>
        </footer>
    </div>
</body>
</html>

