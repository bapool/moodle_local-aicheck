# AI Check for Moodle

AI Check is a Moodle plugin that allows students to get AI-powered feedback on their assignment submissions before final submission.

## Features

- **Student Self-Check**: Students can click "AI Check" button to get instant feedback on their work
- **Automatic Grade Level Detection**: Detects student grade level from username (graduation year)
- **Age-Appropriate Feedback**: AI adjusts language and tone based on detected grade level (3-12)
- **No Grade Storage**: Feedback only - does not affect actual assignment grades
- **Privacy-Conscious**: Processes data through Moodle's AI subsystem
- **Easy Configuration**: Simple system-wide settings, no per-assignment setup needed

## Requirements

- Moodle 4.5 or higher
- PHP 7.4 or higher
- **Moodle AI subsystem configured** with at least one AI provider:
  - Go to Site Administration > AI > AI providers
  - Enable and configure an AI provider (OpenAI, Azure AI, etc.)
  - Ensure the "Generate text" action is enabled for your provider
  - For OpenAI: You'll need an API key and organization ID
  - For Azure: You'll need endpoint URL and API key

### Supported File Types
Students can submit:
- **Online text** submissions
- **File uploads**: 
  - Plain text (.txt)
  - Microsoft Word (.docx)
  - PDF files (.pdf)
- **Google Docs links** (planned)

### Important Notes
- PDF text extraction requires `pdftotext` utility on server
- DOCX extraction works out of the box with PHP ZipArchive
- If file extraction fails, students will see a message indicating the file type isn't supported

## Installation

1. Download or clone this repository
2. Copy the `aicheck` folder to `/path/to/moodle/local/`
3. Visit Site Administration > Notifications to complete installation
4. Configure settings at Site Administration > Plugins > Local plugins > AI Check

## Configuration

### Settings

- **AI Assistant Name**: Customize the name shown on the button (default: "AI")
- **AI Instructions**: System-wide instructions for how AI should provide feedback

### Grade Level Detection

The plugin automatically detects student grade level from their username:
- Looks for 2 or 4 digit graduation year (e.g., "jsmith25" = class of 2025)
- Calculates current grade based on graduation year
- Defaults to grade 9 if no year detected
- Supports grades 3-12

## Usage

### For Students

1. Navigate to your assignment
2. Create or edit your submission
3. Click the "AI Check" button (or custom name)
4. Wait for AI to analyze your work
5. Read the feedback in the popup
6. Revise your work based on suggestions
7. Submit when ready

### For Teachers

No per-assignment configuration needed! The plugin:
- Uses assignment description automatically
- Reads uploaded rubric files (if any)
- Works with all assignment types

## Privacy

AI Check sends the following data to the configured AI provider:
- Student submission text/content
- Assignment name and description
- Rubric/guideline text (if uploaded)
- Detected grade level

No data is permanently stored by this plugin. All processing is done through Moodle's AI subsystem.

## Troubleshooting

### "AI service error" message
- Check that AI providers are configured at Site Administration > AI > AI providers
- Verify at least one provider is enabled
- Ensure "Generate text" action is enabled for your provider
- Check provider API keys are valid and have credits/quota available

### Button doesn't appear
- Student must have submitted work 
- Assignment must not be graded yet
- Student must have `local/aicheck:use` capability
- Teachers/graders will not see the button (student-only)

### "File type not supported" message
For PDF files:
- Server needs `pdftotext` utility installed: `sudo apt-get install poppler-utils`

For DOCX files:
- Should work automatically (uses PHP ZipArchive)

## Support

For issues, questions, or feature requests, please contact:
- **Author**: Brian A. Pool
- **Organization**: National Trail Local Schools
- **License**: GNU GPL v3 or later

## Changelog

See CHANGES.md for version history and updates.
