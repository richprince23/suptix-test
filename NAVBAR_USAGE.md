# NavBar Component Usage

The NavBar component now accepts props for customizing text color, background color, and logo settings.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `textColor` | `string` | `'text-white'` | Tailwind CSS text color class |
| `backgroundColor` | `string` | `'bg-transparent'` | Tailwind CSS background color class |
| `logoSrc` | `string` | `'/suptix.jpg'` | Logo image source URL |
| `logoAlt` | `string` | `'Suptix Logo'` | Logo image alt text |

## Usage Examples

### Default (White text on transparent background)
```vue
<NavBar />
```

### Dark background with white text
```vue
<NavBar 
    text-color="text-white" 
    background-color="bg-black"
/>
```

### Light background with dark text
```vue
<NavBar 
    text-color="text-gray-900" 
    background-color="bg-white"
/>
```

### Semi-transparent background with white text
```vue
<NavBar 
    text-color="text-white" 
    background-color="bg-black/40"
/>
```

### Custom logo
```vue
<NavBar 
    text-color="text-white" 
    background-color="bg-blue-600"
    logo-src="/custom-logo.png"
    logo-alt="Custom Logo"
/>
```

### Combined with CSS classes
```vue
<NavBar 
    text-color="text-white" 
    background-color="bg-gradient-to-r from-purple-600 to-blue-600"
    class="fixed z-20 w-full shadow-lg"
/>
```

## Current Usage in Pages

- **Home.vue**: `text-color="text-white"` + `background-color="bg-black/20"`
- **EventDetails.vue**: `text-color="text-white"` + `background-color="bg-black/40"`
- **EventsPage.vue**: `text-color="text-white"` + `background-color="bg-black"`

## Notes

- The component automatically handles text color variants (muted, hover states)
- Border colors adapt based on the text color
- Mobile menu styling matches the selected color scheme
- All navigation links now use consistent `mutedHover` styling for uniformity
- All Tailwind CSS color classes are supported

## Recent Fixes

- **Home link styling**: Fixed inconsistent text color styling where Home link was using different hover states than other navigation links