# Terminal Echo Command Rule

## Description
When using echo commands in terminal operations, ensure proper escaping and quoting to prevent command failures, especially when outputting to markdown or when using special characters like emojis.

## Best Practices

### Simple Echo Statements
```bash
# GOOD - Simple, reliable
echo "Operation completed successfully"
echo "Current directory: $(pwd)"

# AVOID - Complex formatting that may fail
echo "🎉 OPERATION COMPLETE! 📊 SUMMARY: 🗂️"
```

### Proper Escaping
```bash
# GOOD - Proper escaping for special characters
echo "File: \"config.json\" created"
echo 'Single quotes prevent variable expansion: $VAR'

# GOOD - Multi-line with proper line breaks
echo "Line 1"
echo "Line 2" 
echo "Line 3"

# AVOID - Complex multi-line in single command
echo "Line 1\nLine 2\nLine 3"
```

### Variable Substitution
```bash
# GOOD - Safe variable usage
FILE="example.txt"
echo "Processing file: $FILE"

# GOOD - Protected variable expansion
echo "Current user: ${USER}"
echo "Path: ${PWD}"
```

### Markdown Output
```bash
# GOOD - Simple markdown output
echo "# Header"
echo "- List item 1"
echo "- List item 2"

# AVOID - Complex markdown with special characters
echo "## 🚀 Status: ✅ Complete! 📋"
```

### Error Prevention
```bash
# GOOD - Check before echo with complex content
if [ -f "file.txt" ]; then
    echo "File exists"
else
    echo "File not found"
fi

# GOOD - Use printf for complex formatting
printf "Status: %s\nCount: %d\n" "active" 5
```

## Apply This Rule
- Use simple echo statements without complex formatting
- Prefer multiple simple echo commands over complex single commands
- Properly escape special characters and quotes
- Use printf for complex formatting needs
- Test echo commands before using in scripts
- Avoid emoji and special characters in automated output