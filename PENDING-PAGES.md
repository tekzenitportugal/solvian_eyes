# Solvian Eyes: pending pages (Figma vs live site)

Checked on 2026-10-27 against https://solvian.pt/eyes/ and the local `eyes-child` theme.
Figma: "Solvian Eyes - Ready for Dev", section "Designs - Desktop (24/04/2026)".

## Not built yet
1. **Password recovery** (Figma: Recuperar Senha / Recuperar acesso, 4 frames)
   - Live site uses the default WordPress page: `wp-login.php?action=lostpassword`.
2. **Chatbot** (Figma: icon, hover, open, writing a message, reply, 6 frames)
   - No chat widget or icon on any page. The word only appears as a plan feature line in `inc/planos.php`.
3. **Change password** (Figma: Alterar Password)
   - Account page links to the default plugin "Change Password". No custom screen.
4. **Account page** (`/membership-account/`)
   - Plain Paid Memberships Pro page, not styled like Figma.

## Could not confirm (needs more testing)
5. **Payment confirmation** (Figma: Confirmação de pagamento)
   - `/membership-checkout/membership-confirmation/` is blank without a fresh order. Needs a real test purchase.
6. **Application details with forms** (Figma: Detalhes da candidatura, Default c/ and s/ formulários, Submetido)
   - Template exists (`concurso-detail-pro-sections.php`), but the sample tenders show no form sections. Need a tender with the fields filled in.
7. **"Candidatura Submetida" popup**
   - Not triggered, because testing was read-only.
8. **Pricing frames and tooltip** (Figma: Pricing, _tooltip)
   - Not compared.
9. **Tablet (768px) and mobile (390px) layouts**
   - Not compared.
10. **Visual match** (spacing, colours, fonts) for all built pages
   - Only checked that pages and fields exist.

## Built (confirmed)
Landing page, Concursos list, Login, Registo, Reportar problema, Editar perfil (with Apagar conta), Planos, Checkout, Dashboard with 5 tabs (Free and Pro), tender detail page.
