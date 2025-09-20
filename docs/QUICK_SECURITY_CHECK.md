# 🔒 Quick Security Check - Now Organized!

The `quick_security_check.sh` functionality has been **moved to the tools directory** to maintain a clean root directory structure.

## 🚀 **How to Access:**

### **Direct Access (Recommended)**
```bash
tools/security/quick_security_check.sh
```

### **With Arguments**
```bash
tools/security/quick_security_check.sh --verbose
tools/security/quick_security_check.sh --help
```

## 📍 **File Locations:**

- **Main Script**: `tools/security/quick_security_check.sh`
- **Documentation**: This file (`docs/QUICK_SECURITY_CHECK.md`)

## 🔍 **What It Does:**

The Quick Security Check script verifies:
- ✅ No sensitive files (*.sql, *.dump, *.backup) are tracked in Git
- ✅ Proper .gitignore configuration 
- ✅ Environment file security
- ✅ Docker secrets handling
- ✅ Log file integrity
- ✅ Backup file location and security

## 📚 **Usage Examples:**

```bash
# Basic security check
tools/security/quick_security_check.sh

# Verbose output with detailed information
tools/security/quick_security_check.sh --verbose

# Show help and options
tools/security/quick_security_check.sh --help
```

## 🔗 **Integration with Other Tools:**

The security check integrates well with:
- **`./manage.sh`** - Main management interface
- **`tools/maintenance/refactor_helper_files.sh`** - Helper file organization
- **`./run_dev_env.sh`** - Development environment setup
- **`./reorganize_willow_secure.sh`** - Complete security reorganization

## 🎯 **When to Use:**

- **Before commits** - Verify no sensitive data is being tracked
- **Before deployment** - Ensure security configurations are correct
- **After reorganization** - Validate security after file moves
- **Regular maintenance** - Periodic security health checks

---

**Run `tools/security/quick_security_check.sh` to verify your repository security status!** 🛡️
