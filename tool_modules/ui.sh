#!/bin/bash

# Function to clear the screen and show the header
show_header() {
    if command -v clear >/dev/null; then
        clear
    fi
    echo "==================================="
    echo "WillowCMS Command Runner"
    echo "==================================="
    echo
}

# Function to display the menu
show_menu() {
    echo "Available Commands:"
    echo
    echo "Data Management:"
    echo "  1) Import Default Data (WillowCMS)"
    echo "  2) Export Default Data (WillowCMS)"
    echo "  3) Dump MySQL Database (to host)"
    echo "  4) Load Database from Backup (from host)"
    echo "  5) Clear Database Backups"
    echo
    echo "Internationalization (WillowCMS):"
    echo "  6) Extract i18n Messages"
    echo "  7) Load Default i18n"
    echo "  8) Translate i18n"
    echo "  9) Generate PO Files"
    echo
    echo "Asset Management (WillowCMS):"
    echo "  10) Backup Files Directory"
    echo "  11) Restore Files from Backup"
    echo "  12) Clear Files Backups"
    echo
    echo "System:"
    echo "  13) Clear Cache (WillowCMS)"
    echo "  14) Interactive shell on Willow CMS container"
    echo "  15) Host System Update & Docker Cleanup"
    echo
    echo "Docker Management:"
    echo "  16) View Docker Restart Documentation"
    echo "  17) Restart Docker Environment (Standard)"
    echo "  18) Restart Docker Environment (Soft Reset)"
    echo "  19) Restart Docker Environment (Hard Reset)"
    echo "  0) Exit"
    echo
}