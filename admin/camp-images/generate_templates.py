#!/usr/bin/env python3
"""
Generate campaign template images with gradients and text overlays.
Creates professional-looking banners for Summer Sale, Flash Deal, Back to School, and VIP Member campaigns.
"""

from PIL import Image, ImageDraw, ImageFont
import os

# Template specifications
templates = {
    'Summer Sale': [
        {'file': 'SS1.webp', 'colors': ('#FF6B35', '#F7931E'), 'text': 'Hot Summer Deals — Cool Down with Huge Savings!'},
        {'file': 'SS2.webp', 'colors': ('#00B4D8', '#90E0EF'), 'text': 'Beat the Heat with Unbeatable Summer Offers!'},
        {'file': 'SS3.webp', 'colors': ('#FFB703', '#FB8500'), 'text': 'Sizzling Summer Savings Start Now!'},
        {'file': 'SS4.webp', 'colors': ('#06FFA5', '#00D9FF'), 'text': 'Make Waves with Our Summer Sale — Dive In!'},
        {'file': 'SS5.webp', 'colors': ('#FF006E', '#FFBE0B'), 'text': "Sun's Out, Deals Out — Shop the Summer Sale!"},
    ],
    'Flash Deal': [
        {'file': 'FD1.webp', 'colors': ('#FF0054', '#FF4D00'), 'text': "Lightning Deals — Grab Them Before They're Gone!"},
        {'file': 'FD2.webp', 'colors': ('#FFD60A', '#FFC300'), 'text': 'Flash Sale Alert — Limited Time Only!'},
        {'file': 'FD3.webp', 'colors': ('#7209B7', '#F72585'), 'text': 'Act Fast — Flash Deals Disappear in Hours!'},
        {'file': 'FD4.webp', 'colors': ('#00F5FF', '#00B4D8'), 'text': "Blink and You'll Miss It — Flash Sale Now!"},
        {'file': 'FD5.webp', 'colors': ('#F72585', '#B5179E'), 'text': "Don't Wait — Flash Deals End Tonight!"},
    ],
    'Back to School': [
        {'file': 'BTS1.webp', 'colors': ('#4361EE', '#3A0CA3'), 'text': 'Back to School Savings — Get Ready to Ace the Year!'},
        {'file': 'BTS2.webp', 'colors': ('#2EC4B6', '#FF9F1C'), 'text': 'Smart Savings for Smart Students — Shop Now!'},
        {'file': 'BTS3.webp', 'colors': ('#06FFA5', '#4CC9F0'), 'text': 'Gear Up for Success — Back to School Deals Inside!'},
        {'file': 'BTS4.webp', 'colors': ('#6A4C93', '#1982C4'), 'text': 'Hit the Books — Not Your Budget!'},
        {'file': 'BTS5.webp', 'colors': ('#F77F00', '#D62828'), 'text': 'School Essentials at Unbeatable Prices!'},
    ],
    'VIP Member Reveal': [
        {'file': 'VIP1.webp', 'colors': ('#D4AF37', '#C9A227'), 'text': 'Welcome to the VIP Club — Exclusive Perks Await!'},
        {'file': 'VIP2.webp', 'colors': ('#9D4EDD', '#7209B7'), 'text': "You're VIP Now — Unlock Premium Rewards!"},
        {'file': 'VIP3.webp', 'colors': ('#14213D', '#FCA311'), 'text': 'Exclusive Access Granted — VIP Benefits Inside!'},
        {'file': 'VIP4.webp', 'colors': ('#6A040F', '#DC2F02'), 'text': 'VIP Treatment — Because You Deserve the Best!'},
        {'file': 'VIP5.webp', 'colors': ('#03045E', '#0077B6'), 'text': 'Join the Elite — VIP Membership Activated!'},
    ],
}

def hex_to_rgb(hex_color):
    """Convert hex color to RGB tuple."""
    hex_color = hex_color.lstrip('#')
    return tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))

def create_gradient(width, height, color1, color2):
    """Create a horizontal gradient image."""
    base = Image.new('RGB', (width, height), color1)
    top = Image.new('RGB', (width, height), color2)

    mask = Image.new('L', (width, height))
    mask_data = []
    for y in range(height):
        for x in range(width):
            mask_data.append(int(255 * (x / width)))
    mask.putdata(mask_data)

    base.paste(top, (0, 0), mask)
    return base

def add_text_with_outline(draw, text, position, font, fill_color, outline_color, outline_width=2):
    """Add text with outline for better visibility."""
    x, y = position
    # Draw outline
    for adj_x in range(-outline_width, outline_width + 1):
        for adj_y in range(-outline_width, outline_width + 1):
            draw.text((x + adj_x, y + adj_y), text, font=font, fill=outline_color)
    # Draw text
    draw.text(position, text, font=font, fill=fill_color)

def wrap_text(text, font, max_width):
    """Wrap text to fit within max_width."""
    words = text.split()
    lines = []
    current_line = []

    for word in words:
        test_line = ' '.join(current_line + [word])
        bbox = font.getbbox(test_line)
        width = bbox[2] - bbox[0]

        if width <= max_width:
            current_line.append(word)
        else:
            if current_line:
                lines.append(' '.join(current_line))
            current_line = [word]

    if current_line:
        lines.append(' '.join(current_line))

    return lines

def create_template(filename, color1, color2, text, category):
    """Create a single template image."""
    width, height = 800, 400

    # Create gradient background
    rgb1 = hex_to_rgb(color1)
    rgb2 = hex_to_rgb(color2)
    img = create_gradient(width, height, rgb1, rgb2)

    # Add semi-transparent overlay for better text visibility
    overlay = Image.new('RGBA', (width, height), (0, 0, 0, 80))
    img = img.convert('RGBA')
    img = Image.alpha_composite(img, overlay)

    draw = ImageDraw.Draw(img)

    # Try to load a font, fall back to default if not available
    try:
        # Try Ubuntu font (common on Linux)
        title_font = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', 48)
        category_font = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', 24)
    except:
        try:
            # Try another common font
            title_font = ImageFont.truetype('/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf', 48)
            category_font = ImageFont.truetype('/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf', 24)
        except:
            # Fall back to default
            title_font = ImageFont.load_default()
            category_font = ImageFont.load_default()

    # Add category label at top
    category_bbox = category_font.getbbox(category)
    category_width = category_bbox[2] - category_bbox[0]
    category_height = category_bbox[3] - category_bbox[1]
    category_x = (width - category_width) // 2
    category_y = 40

    add_text_with_outline(draw, category, (category_x, category_y), category_font,
                         (255, 255, 255), (0, 0, 0), 2)

    # Wrap and center main text
    max_text_width = width - 100
    wrapped_lines = wrap_text(text, title_font, max_text_width)

    # Calculate total height of wrapped text
    line_height = 60
    total_text_height = len(wrapped_lines) * line_height

    # Start position for centered text block
    start_y = (height - total_text_height) // 2 + 20

    # Draw each line centered
    for i, line in enumerate(wrapped_lines):
        bbox = title_font.getbbox(line)
        line_width = bbox[2] - bbox[0]
        text_x = (width - line_width) // 2
        text_y = start_y + (i * line_height)

        add_text_with_outline(draw, line, (text_x, text_y), title_font,
                             (255, 255, 255), (0, 0, 0), 3)

    # Convert back to RGB and save
    img = img.convert('RGB')
    img.save(filename, 'WEBP', quality=85, method=6)
    print(f'✓ Created {filename}')

# Main execution
if __name__ == '__main__':
    script_dir = os.path.dirname(os.path.abspath(__file__))

    print('Generating campaign template images...\n')

    for category, template_list in templates.items():
        print(f'Creating {category} templates:')
        for template in template_list:
            filepath = os.path.join(script_dir, template['file'])
            create_template(
                filepath,
                template['colors'][0],
                template['colors'][1],
                template['text'],
                category
            )
        print()

    print('✅ All templates generated successfully!')
    print(f'📁 Location: {script_dir}')
