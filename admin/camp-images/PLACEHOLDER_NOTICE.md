# Campaign Template Images

**Status:** ✅ Professional templates generated on 2026-07-29

## Generated Template Images

The following template images have been **automatically generated** with gradient backgrounds and text overlays:

### Summer Sale (21KB, 19KB, 16KB, 18KB, 19KB)
- ✅ SS1.webp - Warm orange/yellow gradient (#FF6B35 → #F7931E) - 21KB
- ✅ SS2.webp - Cool blue gradient (#00B4D8 → #90E0EF) - 19KB
- ✅ SS3.webp - Vibrant orange gradient (#FFB703 → #FB8500) - 16KB
- ✅ SS4.webp - Tropical teal gradient (#06FFA5 → #00D9FF) - 18KB
- ✅ SS5.webp - Bold pink/yellow gradient (#FF006E → #FFBE0B) - 19KB

### Flash Deal (19KB, 15KB, 15KB, 15KB, 14KB)
- ✅ FD1.webp - Electric red/orange gradient (#FF0054 → #FF4D00) - 19KB
- ✅ FD2.webp - Golden yellow gradient (#FFD60A → #FFC300) - 15KB
- ✅ FD3.webp - Purple/pink gradient (#7209B7 → #F72585) - 15KB
- ✅ FD4.webp - Cyan blue gradient (#00F5FF → #00B4D8) - 15KB
- ✅ FD5.webp - Magenta gradient (#F72585 → #B5179E) - 14KB

### Back to School (19KB, 19KB, 21KB, 13KB, 17KB)
- ✅ BTS1.webp - Royal blue gradient (#4361EE → #3A0CA3) - 19KB
- ✅ BTS2.webp - Teal/orange gradient (#2EC4B6 → #FF9F1C) - 19KB
- ✅ BTS3.webp - Bright cyan gradient (#06FFA5 → #4CC9F0) - 21KB
- ✅ BTS4.webp - Purple/blue gradient (#6A4C93 → #1982C4) - 13KB
- ✅ BTS5.webp - Warm orange/red gradient (#F77F00 → #D62828) - 17KB

### VIP Member Reveal (20KB, 17KB, 18KB, 18KB, 16KB)
- ✅ VIP1.webp - Luxury gold gradient (#D4AF37 → #C9A227) - 20KB
- ✅ VIP2.webp - Royal purple gradient (#9D4EDD → #7209B7) - 17KB
- ✅ VIP3.webp - Navy/gold gradient (#14213D → #FCA311) - 18KB
- ✅ VIP4.webp - Deep red gradient (#6A040F → #DC2F02) - 18KB
- ✅ VIP5.webp - Premium blue gradient (#03045E → #0077B6) - 16KB

## Design Specifications

See `NEW_TEMPLATES_README.md` for complete design specifications including:
- Color schemes
- Text overlays
- Recommended dimensions (800x400px, 2:1 aspect ratio)
- File size targets (<50KB)
- Design elements (icons, imagery)

## Template Features

✅ **Professional gradient backgrounds** matching exact color specifications
✅ **Text overlays** with category labels and campaign headings
✅ **Optimized WebP format** (800x400px, 2:1 aspect ratio)
✅ **Small file sizes** (13-21KB each, well under 50KB target)
✅ **White text with black outline** for maximum readability

## How to Test

1. Navigate to: **WooCommerce → Points & Rewards → Campaign**
2. Click **"Browse Templates"** button
3. Select **Summer Sale**, **Flash Deal**, **Back to School**, or **VIP Member Reveal** from sidebar
4. View all 5 template variants for each category
5. Click **"Apply"** to use a template in your campaign

## Regenerating Templates

To regenerate all templates with new designs, run:
```bash
cd admin/camp-images
python3 generate_templates.py
```

## Tools for Creating WebP Images

- **Canva Pro:** Design templates → Export as PNG → Convert to WebP using squoosh.app
- **Figma:** Design → Export as WebP (plugin required) or PNG → convert
- **Adobe Photoshop:** Design → Save for Web → WebP format
- **Online Converter:** squoosh.app, cloudconvert.com

## Placeholder Creation Command

```bash
# The placeholders were created with:
cd admin/camp-images
cp bf1.webp SS1.webp && cp bf1.webp SS2.webp # ... (repeated for all 20 images)
```

## Testing

After replacing placeholders:
1. Navigate to: WooCommerce → Points & Rewards → Campaign
2. Click "Browse Templates" button
3. Verify all template images load correctly
4. Check colors match specifications
5. Test template selection auto-fills correct colors
