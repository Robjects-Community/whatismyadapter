<?php
declare(strict_types=1);

namespace App\Controller;

use App\Utility\SettingsManager;

/**
 * Author Controller
 *
 * Handles author-related pages like "About the Author", "Hire Me", and social links.
 * Uses the settings system to dynamically display author information.
 */
class AuthorController extends AppController
{
    /**
     * About method
     * 
     * Displays the "About the Author" page using author settings
     *
     * @return void
     */
    public function about(): void
    {
        // Get author settings
        $authorName = SettingsManager::read('Author.fullName', 'Author');
        $authorBio = SettingsManager::read('Author.bio', '');
        $authorEmail = SettingsManager::read('Author.email', '');
        $githubUrl = SettingsManager::read('Author.githubUrl', '');
        $linkedinUrl = SettingsManager::read('Author.linkedinUrl', '');
        $aboutPageContent = SettingsManager::read('Author.aboutPageContent', '');

        $this->set(compact(
            'authorName', 
            'authorBio', 
            'authorEmail', 
            'githubUrl', 
            'linkedinUrl', 
            'aboutPageContent'
        ));
        
        // Set page title
        $this->set('title', __('About the Author'));
        
        // Use the DefaultTheme template
        $this->viewBuilder()->setTemplate('Pages/about_author');
    }

    /**
     * Hire Me method
     * 
     * Displays the "Hire Me" page using author settings
     *
     * @return void
     */
    public function hireMe(): void
    {
        // Get author settings
        $authorName = SettingsManager::read('Author.fullName', 'Author');
        $authorEmail = SettingsManager::read('Author.email', '');
        $hireMeContent = SettingsManager::read('Author.hireMeContent', '');
        $githubUrl = SettingsManager::read('Author.githubUrl', '');
        $linkedinUrl = SettingsManager::read('Author.linkedinUrl', '');

        $this->set(compact(
            'authorName', 
            'authorEmail', 
            'hireMeContent', 
            'githubUrl', 
            'linkedinUrl'
        ));
        
        // Set page title
        $this->set('title', __('Hire Me'));
        
        // Use the DefaultTheme template
        $this->viewBuilder()->setTemplate('Pages/hire_me');
    }

    /**
     * Social method
     * 
     * Displays social media links and follow information
     *
     * @return void
     */
    public function social(): void
    {
        // Get author settings
        $authorName = SettingsManager::read('Author.fullName', 'Author');
        $githubUrl = SettingsManager::read('Author.githubUrl', '');
        $linkedinUrl = SettingsManager::read('Author.linkedinUrl', '');

        $this->set(compact(
            'authorName', 
            'githubUrl', 
            'linkedinUrl'
        ));
        
        // Set page title
        $this->set('title', __('Follow Me'));
        
        // Use the DefaultTheme template
        $this->viewBuilder()->setTemplate('Pages/follow_me');
    }

    /**
     * GitHub method
     * 
     * Displays information about the GitHub repository
     *
     * @return void
     */
    public function github(): void
    {
        // Get author settings
        $githubUrl = SettingsManager::read('Author.githubUrl', 'https://github.com/matthewdeaves/willow');

        $this->set(compact('githubUrl'));
        
        // Set page title
        $this->set('title', __('GitHub Repository'));
        
        // Use the DefaultTheme template
        $this->viewBuilder()->setTemplate('Pages/github');
    }
}