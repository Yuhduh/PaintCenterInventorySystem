---
name: "Grade A Paint Center"
description: "A solid-color paint studio for fast, accountable inventory and sales operations."
colors:
  primary-violet: "#7c3aed"
  primary-violet-hover: "#6d28d9"
  primary-violet-active: "#5b21b6"
  focus-cyan: "#0891b2"
  navigation-cyan: "#0e7490"
  cyan-wash: "#ecfeff"
  rail-blue: "#172554"
  rail-blue-hover: "#1e3a8a"
  page-ink: "#020617"
  heading-ink: "#0f172a"
  body-ink: "#334155"
  muted-ink: "#64748b"
  work-canvas: "#ecfeff"
  work-surface: "#ffffff"
  hairline: "#e2e8f0"
  paint-blue: "#2563eb"
  paint-cyan: "#06b6d4"
  paint-violet: "#7c3aed"
  paint-pink: "#ec4899"
  paint-yellow: "#facc15"
  paint-orange: "#f97316"
  paint-green: "#22c55e"
  paint-red: "#ef4444"
typography:
  display:
    fontFamily: "Manrope, ui-sans-serif, system-ui, sans-serif"
    fontSize: "30px"
    fontWeight: 800
    lineHeight: 1.2
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "Manrope, ui-sans-serif, system-ui, sans-serif"
    fontSize: "24px"
    fontWeight: 800
    lineHeight: 1.25
    letterSpacing: "-0.025em"
  title:
    fontFamily: "Manrope, ui-sans-serif, system-ui, sans-serif"
    fontSize: "20px"
    fontWeight: 800
    lineHeight: 1.4
    letterSpacing: "-0.025em"
  body:
    fontFamily: "Manrope, ui-sans-serif, system-ui, sans-serif"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Manrope, ui-sans-serif, system-ui, sans-serif"
    fontSize: "12px"
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: "0.08em"
rounded:
  compact: "6px"
  action: "8px"
  control: "12px"
  surface: "16px"
  paint-drip: "28px"
  pill: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "20px"
  2xl: "24px"
  3xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary-violet}"
    textColor: "{colors.work-surface}"
    typography: "{typography.body}"
    rounded: "{rounded.control}"
    padding: "10px 16px"
    height: "44px"
  button-primary-hover:
    backgroundColor: "{colors.primary-violet-hover}"
    textColor: "{colors.work-surface}"
    rounded: "{rounded.control}"
  button-secondary:
    backgroundColor: "{colors.work-surface}"
    textColor: "{colors.navigation-cyan}"
    typography: "{typography.body}"
    rounded: "{rounded.control}"
    padding: "10px 16px"
    height: "44px"
  field:
    backgroundColor: "{colors.work-surface}"
    textColor: "{colors.heading-ink}"
    typography: "{typography.body}"
    rounded: "{rounded.control}"
    height: "44px"
  navigation-active:
    backgroundColor: "{colors.primary-violet}"
    textColor: "{colors.work-surface}"
    typography: "{typography.body}"
    rounded: "{rounded.control}"
    height: "44px"
  panel:
    backgroundColor: "{colors.work-surface}"
    textColor: "{colors.body-ink}"
    rounded: "{rounded.surface}"
    padding: "24px"
---

# Design System: Grade A Paint Center

## Overview

**Creative North Star: "The Measured Color Studio"**

Grade A Paint Center is a precise operational studio built from solid fields of paint color. A blue-950 rail and near-black page headers give the authenticated shell authority; a cyan-50 canvas keeps dense work bright; violet makes the primary action unmistakable; and cyan preserves the established focus and navigation voice.

Paint character appears as controlled, flat-color marks rather than gradients or decorative texture. Dashboard metrics, topbar dots, page-header circles, the login composition, and carefully chosen states draw from the same eight-color paint set. The result is vivid without weakening daily scanability.

**Key Characteristics:**

- Violet primary actions and active destinations establish one decisive hierarchy.
- Cyan focus, secondary actions, and navigation support preserve operational continuity.
- A blue-950 rail frames a cyan-50 work canvas and white task surfaces.
- Eight solid paint colors carry identity, metrics, status, and authored login art.
- Manrope spans a compact 10–30px operational type scale.
- Corners progress from 6px utility shapes to 16px surfaces; 28px is paint-drip-only.
- Motion is purposeful, reduced-motion-safe, and never paired with gradients.

## Colors

The system uses solid colors only. No linear, radial, conic, mesh, or simulated gradients are part of the visual language.

### Primary

- **Primary Violet:** The fill for primary buttons, active navigation, and key selected states. Its darker solid steps provide hover and active feedback.
- **Focus Cyan:** The focus border, focus ring, check control, and supporting navigation accent. It does not replace violet as the main call to action.

### Secondary

- **Paint Set:** Blue, cyan, violet, pink, yellow, orange, green, and red form the product's authored paint vocabulary. Use their exact token values for topbar marks, metric identity, paint artwork, alerts, and small accents.
- **Rail Blue:** Blue-950 anchors desktop navigation; blue-900 is its solid hover surface.

### Neutral

- **Page Ink:** Near-black slate for authored headers, totals, and the strongest frame surfaces.
- **Heading Ink:** Deep slate for titles and high-emphasis text on light surfaces.
- **Body Ink:** The default operational reading color.
- **Muted Ink:** Supporting copy, metadata, placeholders, and empty states.
- **Work Canvas:** Cyan-50 behind the authenticated application.
- **Work Surface:** White panels, cards, toolbars, inputs, and modals.
- **Hairline:** Slate-200 for low-contrast dividers and field boundaries.

### Named Rules

**The Violet Leads Rule.** Violet owns the primary action and active destination. Cyan owns focus and support. Never reverse these roles without a product-level decision.

**The Solid Paint Rule.** Every fill is a solid color. Do not introduce gradients, even for login art, metrics, hero headers, overlays, or decorative paint effects.

**The Eight-Paint Rule.** Authored paint color comes from blue, cyan, violet, pink, yellow, orange, green, and red. Tints may support legibility, but new hues must not dilute the set.

## Typography

**Display Font:** Manrope (with the system sans-serif stack)
**Body Font:** Manrope (with the system sans-serif stack)

**Character:** Manrope supplies geometric confidence without sacrificing table and form legibility. Extra-bold titles, tabular numerals, compact uppercase labels, and restrained tracking make dense operational information quick to parse.

### Hierarchy

- **Display** (800, 30px, 1.2): Desktop page titles and metric numerals at the largest authenticated size.
- **Headline** (800, 24px, 1.25): Mobile page titles and strong authored headings.
- **Title** (800, 20px, 1.4): Modal titles and major section headings.
- **Section** (800, 16px, 1.5): Panel titles, topbar titles on larger screens, and compact card headings.
- **Body** (400–700, 14px, 1.5): Controls, table cells, descriptions, links, and core reading text.
- **Label** (700, 12px, 1.25): Metric labels, metadata, status chips, and helper text.
- **Table Header** (700, 11px, 0.08em tracking): Dense uppercase column headings on the solid dark header row.
- **Micro Label** (700–800, 10px, 0.1–0.12em tracking): Navigation group captions, review flags, and SKU badges only.

### Named Rules

**The Operational Scale Rule.** Use only the observed 10, 11, 12, 14, 16, 20, 24, and 30px sizes in authenticated UI. Choose weight before inventing another size.

## Layout

The authenticated shell uses a responsive rail-and-stage model. From 640px upward, a sticky blue-950 rail occupies 256px and can collapse to 80px; the main stage scrolls independently below an 80px sticky white topbar. Mobile replaces the rail with a 64px white header and an explicit expandable menu.

Pages use 16px mobile, 24px tablet, and 32px desktop gutters. Standard pages cap at 1280px; the shared topbar and point-of-sale page reach 1600px. Vertical page rhythm is 24px. Panels generally use 20px padding on compact screens and 24px on larger screens.

Dashboard metrics form a single column before becoming a three-column row at 640px. The point-of-sale workbench becomes a product stage plus a 360px sticky cart at 1024px; product cards progress from one to two and then three columns. Toolbars wrap or become explicit grids rather than shrinking controls below their usable size.

Dense tables retain their meaning through horizontal overflow. Their solid slate-900 headers stay compact, cells use 16px by 12px padding, and rows use hairlines plus a cyan-50 hover. Mobile should recompose a workflow only when row identity, totals, and actions remain equally clear.

**The Workbench Rule.** Keep filters, products, cart, totals, and actions in one readable operational sequence. Responsive changes may alter composition, never task order.

## Elevation & Depth

Depth combines solid tonal layering with broad, low-opacity directional shadows. White panels lift from the cyan canvas, metric cards and product cards use lighter local lift, the rail casts sideways depth, and modals use the strongest shadow over a solid translucent slate backdrop. No shadow may simulate a gradient.

### Shadow Vocabulary

- **Surface Lift** (`0 18px 45px -32px rgba(15, 23, 42, 0.45)`): Standard work surfaces.
- **Panel Lift** (`0 18px 45px -34px rgba(15, 23, 42, 0.50)`): Dense panels and table containers.
- **Metric Lift** (`0 18px 42px -32px rgba(15, 23, 42, 0.50)`): Dashboard metrics.
- **Rail Cast** (`12px 0 40px -32px rgba(2, 8, 23, 0.90)`): Desktop navigation edge.
- **Violet Action Lift** (`0 10px 22px -14px rgba(109, 40, 217, 0.90)`): Primary buttons.
- **Modal Lift** (`0 32px 80px -36px rgba(2, 8, 23, 0.85)`): Confirmation and custom-mix dialogs.

### Named Rules

**The Directional Depth Rule.** Use one broad shadow to establish hierarchy, then rely on solid color, spacing, and hairlines inside the surface.

## Shapes

The form language moves from compact to containing: 6px for terse validation notices, 8px for small inline actions and badges, 12px for buttons, inputs, navigation items, marks, and feedback blocks, and 16px for cards, panels, headers, toolbars, metric tiles, and modals. Full circles and pills are reserved for status chips, paint dots, icons, and compact counters.

The 28px radius is exclusive to the lower edge of the login paint drips, where it combines with organic percentage radii. It is not a card, button, input, modal, or navigation radius.

**The Radius Has Meaning Rule.** Use 6/8/12/16px for interface geometry. Use 28px only for paint drips and full rounding only when the element is semantically a dot, chip, or circular mark.

## Components

Shared `app-*` patterns define the authenticated grammar. Page-local variants may select from the paint set, but they must retain the shared control, state, spacing, and radius rules.

### Buttons

- **Primary:** Solid violet-600, white text, 12px corners, 44px minimum height, bold 14px label, and a restrained violet shadow. Hover uses violet-700; active uses violet-800 or a one-pixel press.
- **Secondary:** White with cyan-800 text and a cyan-200 inset ring. Hover moves to cyan-50 and cyan-950.
- **Contextual:** Dashboard header actions may use solid cyan-400 or blue-900 when the dark header already establishes context; only one action remains visually dominant.
- **Danger / Warning:** Confirmation actions use solid red-600 or amber-600 with explicit text and processing state.
- **Focus / Disabled:** All actions retain the 3px translucent cyan focus outline. Disabled buttons keep their geometry, suppress hover, use the not-allowed cursor, and reduce opacity to 50–60%.

### Navigation

- **Desktop Rail:** Solid blue-950 with blue-100 labels, blue-900 hover, and violet-600 active items. The active state includes `aria-current` and may carry a violet directional shadow.
- **Mobile Menu:** White with cyan-950 labels, cyan-50 hover, and violet-600 active items. Every target remains at least 44px high.
- **Brand Mark:** Yellow-300 tile with a cyan mark; it remains solid and uses 12px corners.

### Metrics

- **Container:** White 16px card with one soft shadow and a pale solid circular corner accent.
- **Identity:** A blue, orange, or green square icon and matching label identifies the metric; the value is 30px extra-bold with tabular numerals.
- **Exception:** A 10px pill may call out review state without replacing the numeric value.

### Cards / Containers

- **Panels:** White, 16px corners, 20–24px padding, one directional shadow.
- **Product Cards:** White with an inset slate ring; hover lifts by two pixels and shifts the ring to cyan-400.
- **Totals:** The primary total block is solid page ink with white text; positive change blocks are solid emerald-600.

### Inputs / Fields

- **Style:** White background, slate-300 border, 12px corners, 14px text, and a 44px minimum height.
- **Focus:** Border and ring use cyan-600 in addition to the global visible outline.
- **Error / Disabled:** Errors use solid red tints and adjacent text. Disabled fields preserve labels and layout, reduce emphasis, and remain distinguishable without color alone.

### Dense Tables

- **Headers:** Solid slate-900, 11px bold uppercase labels, 0.08em tracking, and slate-100 text.
- **Rows:** 14px text, hairline separators, cyan-50 hover, tabular numerals for quantities and money.
- **Responsive Behavior:** Keep the table horizontally scrollable when its relationships are genuinely tabular; never squeeze columns into illegibility.

### Confirmation Modal

- **Layer:** Solid translucent slate-950 backdrop with blur and a white 16px dialog using the strongest shadow.
- **Message:** A 40px circular red or amber icon leads a 20px title and 14px explanatory copy.
- **Actions:** Cancel uses the shared secondary button. Confirm uses a solid red or amber button, disables during processing, and changes its label to “Processing…”.

### Feedback States

- **Loading:** Disable the initiating control, name the pending action, and show a local spinner only where the wait originates.
- **Empty:** State whether data is absent or filters found no match; dashboard empty states may pair copy with a small solid semantic icon.
- **Focus:** Apply the cyan outline consistently to controls, links, product cards, modal actions, and navigation.
- **Selected / Active:** Use violet for persistent selection and active destinations; use cyan for focus and temporary hover support.

### Login Paint Workflow

The approved login remains the expressive edge of the same visual world. Its solid paint drips enter over 3.8 seconds with delays up to 0.5 seconds, and the falling droplet resolves within the same opening sequence. The broader studio background may use slow, organic motion for solid blobs, droplets, bubbles, tools, and swatches; it never uses gradients and never moves the form in response to pointer position. The card arrives once over 700ms. Reduced-motion users receive the final still composition, with droplets hidden and all authored animation disabled.

Authenticated pages use only a 320ms arrival and 150–300ms interaction/modal transitions. They do not inherit the login's ambient paint motion.

**The Authored Motion Boundary Rule.** Organic paint motion belongs to login. Authenticated work surfaces use short state and page-entry transitions only.

## Do's and Don'ts

### Do:

- **Do** use violet for primary actions and active navigation, with cyan for focus and support.
- **Do** use only solid fills drawn from the documented palette and paint set.
- **Do** preserve blue-950 rail, cyan-50 canvas, and white work surfaces across authenticated screens.
- **Do** use the observed 10/11/12/14/16/20/24/30px type scale and 6/8/12/16px interface radii.
- **Do** preserve dense tables, explicit overflow, 44px targets, visible focus, and named loading/empty/disabled states.
- **Do** keep login paint motion separate from authenticated workflows and honor reduced motion.

### Don't:

- **Don't** introduce gradients, blended color fields, glassy chromatic effects, or gradient-like shadow stacks.
- **Don't** use cyan as the default primary button or violet as the default focus ring.
- **Don't** use the 28px paint-drip radius on interface controls or containers.
- **Don't** make every surface float; nested content uses spacing and hairlines.
- **Don't** compress operational tables or the POS cart until labels, values, or actions become ambiguous.
- **Don't** add ambient looping paint motion to authenticated pages.
