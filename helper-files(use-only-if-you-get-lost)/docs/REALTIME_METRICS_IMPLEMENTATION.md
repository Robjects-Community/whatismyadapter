# Real-time AI Metrics Dashboard Implementation

## Overview

This implementation adds comprehensive real-time monitoring capabilities to the AI Metrics Dashboard in the Willow CMS. The dashboard now updates automatically every 10 seconds, providing live visibility into AI API usage, costs, and performance metrics.

## Features Implemented

### 1. Real-time Data Updates
- **AJAX Polling**: Dashboard updates every 10 seconds via JavaScript
- **Live/Offline Indicators**: Visual indicators showing connection status
- **Automatic Refresh**: Metrics are refreshed without page reload
- **Timestamp Display**: Shows when data was last updated

### 2. Dynamic Timeframe Selection
- **Multiple Timeframes**: 1H, 24H, 7D, 30D options
- **On-demand Updates**: Instant data refresh when timeframe changes
- **Period Labels**: Dynamic label updates based on selected timeframe

### 3. Enhanced Metrics Display
- **Total API Calls**: Real-time count with formatting
- **Success Rate**: Color-coded percentage (green/yellow/red)
- **Total Cost**: Live cost tracking with currency formatting
- **Rate Limit Status**: Current usage vs. limits with warnings

### 4. Queue Monitoring
- **Active Jobs**: Count of currently running queue jobs
- **Pending Jobs**: Number of jobs waiting to be processed
- **Real-time Status**: Updates reflect actual queue state

### 5. Activity Visualization
- **Sparkline Chart**: Visual representation of API calls per minute
- **Recent Activity Feed**: Live feed of the last 5 AI operations
- **Task Type Breakdown**: Dynamic table with service indicators

### 6. Error Handling & Reliability
- **Connection Monitoring**: Automatic detection of API failures
- **Graceful Degradation**: Falls back to offline mode on errors
- **Retry Logic**: Attempts to reconnect automatically
- **User Feedback**: Clear indicators when updates fail

## Technical Implementation

### Backend Components

#### 1. Controller Enhancement (`AiMetricsController.php`)
```php
/**
 * Real-time metrics API endpoint for AJAX updates
 */
public function realtimeData(): ?Response
{
    // Handles GET requests with timeframe parameter
    // Returns JSON with comprehensive metrics data
}
```

#### 2. New Route Configuration (`routes.php`)
```php
$routes->connect('/ai-metrics/realtime-data', [
    'controller' => 'AiMetrics', 
    'action' => 'realtimeData'
]);
```

#### 3. Data Aggregation
- **Cost Calculations**: Aggregated by date range
- **Task Summaries**: Grouped by task type with averages
- **Queue Status**: Live queue job counts
- **Sparkline Data**: Minute-by-minute API call counts

### Frontend Components

#### 1. Dashboard Template Updates (`dashboard.php`)
- Added live indicators and timeframe controls
- Implemented specific element IDs for JavaScript targeting
- Created new sections for queue status and activity

#### 2. JavaScript Real-time Engine
```javascript
// Core functionality
- initializeRealTime(): Sets up polling interval
- updateMetrics(): Fetches data via AJAX
- updateDashboard(): Updates all dashboard elements
- stopRealTime(): Cleanup on errors/page unload
```

#### 3. Dynamic UI Updates
- **Card Updates**: Specific targeting by element ID
- **Table Refresh**: Complete task metrics table rebuild
- **Activity Feed**: Rolling list of recent operations
- **Visual Indicators**: Status badges and color coding

## Data Flow

```
User loads dashboard
       ↓
JavaScript initializes (10s interval)
       ↓
AJAX call to /admin/ai-metrics/realtime-data
       ↓
Controller fetches fresh data from:
  - ai_metrics table
  - queue_jobs table  
  - rate limit service
       ↓
JSON response with structured data
       ↓
JavaScript updates DOM elements
       ↓
Process repeats every 10 seconds
```

## API Response Structure

```json
{
  "success": true,
  "timestamp": 1635789123,
  "timeframe": "30d",
  "data": {
    "totalCalls": 1234,
    "successRate": 95.2,
    "totalCost": 45.67,
    "currentUsage": {
      "current": 15,
      "limit": 100,
      "remaining": 85
    },
    "queueStatus": {
      "active": 2,
      "pending": 8
    },
    "taskMetrics": [...],
    "recentActivity": [...],
    "sparkline": [5, 3, 8, 12, ...]
  }
}
```

## Monitoring Capabilities

### 1. Real-time Metrics
- **API Call Volume**: Live tracking of request counts
- **Cost Monitoring**: Real-time expense tracking
- **Performance Metrics**: Execution times and success rates
- **Error Tracking**: Immediate visibility into failures

### 2. Queue Health
- **Processing Status**: Active vs pending jobs
- **Bottleneck Detection**: Queue backup indicators
- **Throughput Monitoring**: Jobs processed over time

### 3. Usage Patterns
- **Service Breakdown**: Google vs Anthropic usage
- **Time-based Analysis**: Activity patterns by timeframe
- **Cost Distribution**: Spending by service type

## Configuration

### JavaScript Settings
```javascript
const POLL_INTERVAL = 10000; // 10 seconds
const ACTIVITY_LIMIT = 5;     // Recent activity items
const SPARKLINE_HOURS = 1;    // Sparkline data window
```

### PHP Configuration
```php
// Database queries optimized for real-time performance
// Caching disabled for live data accuracy
// Error handling for graceful degradation
```

## Usage Instructions

### Accessing the Dashboard
1. Navigate to `/admin/ai-metrics/dashboard`
2. Dashboard automatically starts live updates
3. Use timeframe buttons to change date range
4. Monitor live/offline indicator for status

### Interpreting the Data
- **Green Success Rate**: 95%+ (excellent)
- **Yellow Success Rate**: 85-95% (warning)
- **Red Success Rate**: <85% (critical)
- **Rate Limit Warning**: <10 remaining calls

### Troubleshooting
- **Offline Indicator**: Check network connectivity
- **No Data Updates**: Verify API endpoints are accessible
- **High Error Rates**: Check AI service configurations

## Performance Considerations

### Frontend Optimization
- Efficient DOM updates using element IDs
- Minimal data transfer via JSON API
- Automatic cleanup on page unload

### Backend Optimization
- Indexed database queries for fast aggregation
- Minimal data processing in controller
- Efficient JSON serialization

### Scalability
- Polling interval adjustable for different loads
- Database queries optimized for large datasets
- Caching strategies available for high-traffic sites

## Future Enhancements

### Planned Features
- WebSocket integration for true real-time updates
- Advanced filtering and search capabilities
- Export functionality for metrics data
- Mobile-responsive design improvements

### Performance Improvements
- Redis caching for frequently accessed data
- Database query optimization
- Progressive loading for large datasets

## Testing

### Manual Testing
1. Load dashboard and verify live indicator
2. Run queue workers to see real-time updates
3. Change timeframes and verify data refresh
4. Test error handling by blocking API endpoint

### Automated Testing
- Unit tests for controller methods
- JavaScript tests for DOM manipulation
- Integration tests for API endpoints

## Dependencies

### Required Services
- MySQL database (ai_metrics table)
- Queue system (for job monitoring)
- Redis (for rate limiting)

### Browser Requirements
- Modern browser with JavaScript enabled
- Fetch API support (or polyfill)
- JSON parsing capabilities

---

This implementation provides comprehensive real-time monitoring of AI API usage, enabling proactive management of costs, performance, and service reliability.
