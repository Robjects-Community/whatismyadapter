# File Management Module

## Overview
The File Management module provides utilities for maintaining and reorganizing project files within the WillowCMS development environment. This module is integrated into the main `./manage.sh` system and provides a dedicated menu for file-related operations.

## Access
Access the file management utilities through the main management script:
```bash
./manage.sh
```
Select option **25 - File Management Menu** from the main menu.

## Available Operations

### 1. Refactor Helper Files
Reorganizes and updates helper files throughout the project structure. This operation:
- Moves files to appropriate directories
- Updates import/require statements
- Creates compatibility wrappers for moved files
- Maintains backward compatibility

**Usage**: Select option **1** from the File Management menu.

**What it does**:
- Scans for misplaced helper files
- Moves them to standardized locations (`tools/`, `docs/`, etc.)
- Updates all references in source code
- Creates forwarding scripts for legacy paths

### 2. Clean Temporary Files
Removes temporary files, cache, and build artifacts to free up space and ensure clean builds.

**Usage**: Select option **2** from the File Management menu.

**What it cleans**:
- PHP temporary files
- CakePHP cache directories
- Build artifacts
- Log files (with backup option)
- IDE temporary files

## Integration with Main System
The file management functionality is implemented as a module (`tool_modules/file_management.sh`) that integrates seamlessly with the existing management system. This ensures:

- Consistent user experience
- Proper logging and error handling
- Integration with other project tools
- Centralized access point

## Legacy Compatibility
For users who were previously using the standalone `refactor_helper_files.sh` script:

1. The original script has been moved to `tools/legacy-helpers/refactor_helper_files.sh.legacy`
2. A compatibility wrapper at `tools/refactor_helper_files.sh` redirects to the new system
3. All functionality is preserved but now integrated into the main management interface

## Best Practices

### Before Using File Refactoring
1. Ensure all changes are committed to git
2. Run the security check: `tools/quick_security_check.sh`
3. Make sure the development environment is stable

### After File Operations
1. Test affected functionality
2. Run unit tests if available
3. Verify import/require statements work correctly
4. Check that moved files are accessible from their new locations

## File Organization Standards
The module follows these organization principles:

- **tools/**: Executable scripts and utilities
- **docs/**: Documentation files
- **tool_modules/**: Modular functionality for the management system
- **config/**: Configuration files
- **plugins/**: CakePHP plugins and themes

## Troubleshooting

### Common Issues

**Issue**: "File not found after refactoring"
**Solution**: Check the compatibility wrappers in `tools/` directory, or use the new integrated path.

**Issue**: "Import statements not updated"
**Solution**: The refactoring process should handle this automatically. If missed, manually update the paths.

**Issue**: "Legacy script doesn't work"
**Solution**: Use `./manage.sh` → option 25 → option 1 instead of the standalone script.

### Getting Help
If you encounter issues:
1. Check the logs in the management system
2. Verify file permissions
3. Ensure you have write access to the project directories
4. Use `./manage.sh` → Help/About option for system status

## Technical Details

### Module Structure
```
tool_modules/
└── file_management.sh    # Main module file
    ├── refactor_helper_files()    # Core refactoring logic
    ├── clean_temp_files()         # Cleanup utilities
    └── file_management_menu()     # User interface
```

### Dependencies
- Bash 4.0+ (for associative arrays)
- Standard Unix utilities (find, sed, grep)
- Git (for repository operations)

### Configuration
The module respects the main system configuration and project structure defined in the WillowCMS environment.