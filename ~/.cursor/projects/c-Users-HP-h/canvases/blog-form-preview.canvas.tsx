import { Card, CardHeader, Text, Flex, Badge } from "cursor/canvas";

const COLORS = {
  bg: "#FDFAF6",
  card: "#FFFFFF",
  primary: "#B85D3F",
  primaryDark: "#9A4A30",
  gold: "#DCB159",
  goldBg: "rgba(220, 177, 89, 0.12)",
  text: "#2C2C2C",
  textLight: "#6B5E55",
  muted: "#9A8E85",
  border: "#EBE3DA",
  danger: "#DC2626",
  success: "#10B981",
  bgAlt: "#F5EDE4",
};

function Field({ label, children, full }: { label: string; children: React.ReactNode; full?: boolean }) {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 5, gridColumn: full ? "1 / -1" : undefined }}>
      <span style={{ fontSize: 12, fontWeight: 700, color: COLORS.textLight, letterSpacing: 0.3 }}>{label}</span>
      {children}
    </div>
  );
}

function TextInput({ placeholder, value }: { placeholder: string; value?: string }) {
  return (
    <div style={{
      padding: "10px 14px", border: `1.5px solid ${COLORS.border}`, borderRadius: 10,
      fontSize: 14, color: value ? COLORS.text : COLORS.muted, background: "#fff",
      fontFamily: "Inter, system-ui, sans-serif",
    }}>
      {value || placeholder}
    </div>
  );
}

function Textarea({ placeholder, rows = 3 }: { placeholder: string; rows?: number }) {
  return (
    <div style={{
      padding: "10px 14px", border: `1.5px solid ${COLORS.border}`, borderRadius: 10,
      fontSize: 14, color: COLORS.muted, background: "#fff", minHeight: rows * 24,
      fontFamily: "Inter, system-ui, sans-serif",
    }}>
      {placeholder}
    </div>
  );
}

function UploadBox({ icon, label, sublabel }: { icon: string; label: string; sublabel: string }) {
  return (
    <div style={{
      display: "flex", alignItems: "center", gap: 14,
      border: `1.5px solid ${COLORS.border}`, borderRadius: 12, padding: "12px 14px",
      background: COLORS.bg,
    }}>
      <div style={{
        width: 90, height: 70, borderRadius: 8, background: COLORS.bgAlt,
        display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", gap: 4,
        border: `1.5px dashed ${COLORS.border}`, flexShrink: 0,
      }}>
        <span style={{ fontSize: 24 }}>{icon}</span>
        <span style={{ fontSize: 10, color: COLORS.muted }}>Aucun fichier</span>
      </div>
      <div style={{ display: "flex", flexDirection: "column", gap: 6, flex: 1 }}>
        <span style={{ fontSize: 13, fontWeight: 600, color: COLORS.text }}>{sublabel}</span>
        <div style={{
          display: "inline-flex", alignItems: "center", gap: 6, padding: "7px 16px",
          background: `linear-gradient(135deg, ${COLORS.primary}, ${COLORS.primaryDark})`,
          color: "#fff", borderRadius: 50, fontSize: 12, fontWeight: 600, width: "fit-content",
          boxShadow: "0 4px 14px rgba(184,93,63,0.25)",
        }}>
          <span>⬆</span> {label}
        </div>
      </div>
    </div>
  );
}

function BlockCard({ type, icon, title, fields }: { type: string; icon: string; title: string; fields: { label: string; value: string; upload?: boolean }[] }) {
  return (
    <div style={{
      border: `1px solid ${COLORS.border}`, borderRadius: 10, background: "#fff",
      boxShadow: "0 2px 8px rgba(0,0,0,0.04)", overflow: "hidden",
    }}>
      <div style={{
        display: "flex", alignItems: "center", gap: 8, padding: "10px 12px",
        background: "#FBF7F2", borderBottom: `1px solid ${COLORS.border}`,
      }}>
        <span style={{ color: COLORS.gold, fontSize: 16 }}>{icon}</span>
        <span style={{ fontSize: 13, fontWeight: 700, color: COLORS.text }}>{title}</span>
        <div style={{ flex: 1 }} />
        <div style={{ display: "flex", gap: 4 }}>
          <span style={{ fontSize: 14, color: COLORS.muted, cursor: "pointer" }}>▲</span>
          <span style={{ fontSize: 14, color: COLORS.muted, cursor: "pointer" }}>▼</span>
          <span style={{ fontSize: 14, color: COLORS.danger, cursor: "pointer" }}>✕</span>
        </div>
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "10px 16px", padding: 14 }}>
        {fields.map((f, i) => (
          <Field key={i} label={f.label} full={f.upload}>
            {f.upload ? (
              <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
                <div style={{
                  flex: 1, padding: "8px 12px", border: `1px solid ${COLORS.border}`,
                  borderRadius: 8, fontSize: 13, color: COLORS.muted, background: "#fff",
                }}>
                  {f.value}
                </div>
                <div style={{
                  width: 36, height: 36, border: `1.5px solid ${COLORS.border}`, borderRadius: 8,
                  background: COLORS.bg, display: "flex", alignItems: "center", justifyContent: "center",
                  color: COLORS.primary, fontSize: 16, cursor: "pointer",
                }}>
                  ⬆
                </div>
              </div>
            ) : (
              <div style={{
                padding: "8px 12px", border: `1px solid ${COLORS.border}`, borderRadius: 8,
                fontSize: 13, color: f.value ? COLORS.text : COLORS.muted, background: "#fff",
              }}>
                {f.value || "…"}
              </div>
            )}
          </Field>
        ))}
      </div>
    </div>
  );
}

export default function BlogFormPreview() {
  return (
    <Flex direction="column" gap={20} style={{ padding: 24, fontFamily: "Inter, system-ui, sans-serif" }}>
      {/* Header */}
      <Flex direction="column" gap={4}>
        <Text style={{ fontSize: 22, fontWeight: 800, color: COLORS.text }}>
          ✏️ Aperçu du formulaire blog simplifié
        </Text>
        <Text style={{ fontSize: 13, color: COLORS.muted }}>
          Ce à quoi l'administrateur sera confronté — aucun code HTML ni CSS visible.
        </Text>
      </Flex>

      {/* Main form card */}
      <Card style={{ background: COLORS.card, border: `1px solid ${COLORS.border}`, borderRadius: 16, boxShadow: "0 4px 20px rgba(0,0,0,0.06)", overflow: "hidden" }}>
        <CardHeader style={{ background: COLORS.bgAlt, padding: "16px 20px", borderBottom: `1px solid ${COLORS.border}` }}>
          <Flex align="center" gap={8}>
            <Text style={{ fontSize: 16, fontWeight: 700, color: COLORS.text }}>📝 Formulaire article</Text>
            <Badge style={{ background: COLORS.goldBg, color: COLORS.primary, fontSize: 11, padding: "3px 10px", borderRadius: 50, fontWeight: 600 }}>
              Éditeur visuel uniquement
            </Badge>
          </Flex>
        </CardHeader>

        <div style={{ padding: 20 }}>
          {/* Row 1: Titre */}
          <div style={{ marginBottom: 16 }}>
            <Field label="Titre">
              <TextInput placeholder="Titre de l'article" value="Découvrez les plus beaux sites du Bénin" />
            </Field>
          </div>

          {/* Row 2: Sous-titre */}
          <div style={{ marginBottom: 16 }}>
            <Field label="Sous-titre">
              <TextInput placeholder="Courte description" value="Un voyage inoubliable à travers la culture et l'histoire" />
            </Field>
          </div>

          {/* Row 3: Slug */}
          <div style={{ marginBottom: 16 }}>
            <Field label="Slug (URL)">
              <TextInput placeholder="titre-de-l-article" value="decouvrez-plus-beaux-sites-benin" />
            </Field>
          </div>

          {/* Row 4: Image + Vidéo side by side */}
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16, marginBottom: 16 }}>
            <Field label="Image de l'article">
              <UploadBox icon="🖼" label="Choisir une image" sublabel="JPG, PNG, GIF, WEBP — max 8 Mo" />
            </Field>
            <Field label="Vidéo de l'article (optionnel)">
              <UploadBox icon="🎬" label="Choisir une vidéo" sublabel="MP4, WebM — max 50 Mo" />
            </Field>
          </div>

          {/* Row 5: Extrait */}
          <div style={{ marginBottom: 16 }}>
            <Field label="Extrait (affiché dans la liste)" full>
              <Textarea placeholder="Courte description pour la carte..." rows={2} />
            </Field>
          </div>

          {/* Row 6: Éditeur visuel */}
          <div style={{ marginBottom: 16 }}>
            <Field label="Contenu de l'article" full>
              {/* Toolbar */}
              <div style={{
                display: "flex", alignItems: "center", gap: 6, padding: "8px 12px",
                background: COLORS.bgAlt, borderRadius: "10px 10px 0 0", border: `1px solid ${COLORS.border}`, borderBottom: "none",
                flexWrap: "wrap",
              }}>
                <span style={{ fontSize: 12, fontWeight: 700, color: COLORS.textLight, marginRight: 4 }}>Ajouter un bloc :</span>
                {[
                  { icon: "Aa", label: "Section" },
                  { icon: "☰", label: "Liste" },
                  { icon: "❝", label: "Citation" },
                  { icon: "🖼", label: "Image" },
                  { icon: "🎬", label: "Vidéo" },
                  { icon: "—", label: "Séparateur" },
                ].map((b) => (
                  <div key={b.label} style={{
                    padding: "5px 12px", borderRadius: 50, border: `1.5px solid ${COLORS.primary}`,
                    fontSize: 11, fontWeight: 600, color: COLORS.primary, cursor: "pointer",
                    display: "flex", alignItems: "center", gap: 4, background: "#fff",
                  }}>
                    {b.icon} {b.label}
                  </div>
                ))}
              </div>

              {/* Blocks */}
              <div style={{
                border: `1px solid ${COLORS.border}`, borderTop: "none",
                borderRadius: "0 0 10px 10px", padding: 14, display: "flex", flexDirection: "column", gap: 12,
                background: "#FDFAF6",
              }}>
                <BlockCard
                  type="section" icon="Aa" title="Section"
                  fields={[
                    { label: "Titre de la section", value: "Les incontournables du Bénin" },
                    { label: "Texte (une phrase par ligne)", value: "Le Bénin regorge de sites historiques et naturels…" },
                    { label: "Niveau du titre", value: "Titre principal (H2)" },
                    { label: "Couleur du titre", value: "#1a1a1a" },
                  ]}
                />
                <BlockCard
                  type="image" icon="🖼" title="Image"
                  fields={[
                    { label: "URL de l'image", value: "uploads/blog/blog-img-1724883200.jpg", upload: true },
                    { label: "Légende (optionnelle)", value: "Porte du Non-Retour" },
                    { label: "Alignement", value: "Centré" },
                    { label: "Largeur (%)", value: "100" },
                  ]}
                />
                <BlockCard
                  type="quote" icon="❝" title="Citation"
                  fields={[
                    { label: "Texte de la citation", value: "Le Bénin est un trésor culturel d'Afrique" },
                    { label: "Auteur (optionnel)", value: "Guide local" },
                  ]}
                />
              </div>
            </Field>
          </div>

          {/* Row 7: Statut */}
          <div style={{ marginBottom: 16 }}>
            <Field label="Statut">
              <div style={{
                padding: "10px 14px", border: `1.5px solid ${COLORS.border}`, borderRadius: 10,
                fontSize: 14, color: COLORS.text, background: "#fff", display: "flex", justifyContent: "space-between", alignItems: "center",
              }}>
                <span>Publié</span>
                <span style={{ color: COLORS.muted }}>▾</span>
              </div>
            </Field>
          </div>

          {/* Action buttons */}
          <div style={{ display: "flex", gap: 8, marginTop: 12, flexWrap: "wrap" }}>
            <div style={{
              display: "inline-flex", alignItems: "center", gap: 6, padding: "8px 18px",
              background: `linear-gradient(135deg, ${COLORS.primary}, ${COLORS.primaryDark})`,
              color: "#fff", borderRadius: 50, fontSize: 12, fontWeight: 600,
              boxShadow: "0 4px 14px rgba(184,93,63,0.25)",
            }}>
              💾 Enregistrer
            </div>
            <div style={{
              display: "inline-flex", alignItems: "center", gap: 6, padding: "8px 18px",
              background: "transparent", color: COLORS.primary, borderRadius: 50, fontSize: 12, fontWeight: 600,
              border: `1.5px solid ${COLORS.primary}`,
            }}>
              👁 Aperçu
            </div>
            <div style={{
              display: "inline-flex", alignItems: "center", gap: 6, padding: "8px 18px",
              background: "transparent", color: COLORS.textLight, borderRadius: 50, fontSize: 12, fontWeight: 600,
              border: `1.5px solid ${COLORS.border}`,
            }}>
              Annuler
            </div>
          </div>
        </div>
      </Card>

      {/* Summary */}
      <Card style={{ background: COLORS.goldBg, border: `1px dashed ${COLORS.gold}`, borderRadius: 12, padding: "14px 18px" }}>
        <Text style={{ fontSize: 13, color: COLORS.textLight, lineHeight: 1.6 }}>
          <strong>Résumé :</strong> L'administrateur voit des <strong>champs simples</strong> (titre, sous-titre, slug), des <strong>boutons d'upload</strong> pour l'image et la vidéo, et un <strong>éditeur visuel par blocs</strong>. Aucun onglet "Code HTML", aucun champ CSS. Tout est intuitif comme WordPress.
        </Text>
      </Card>
    </Flex>
  );
}
