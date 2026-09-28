<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">QR Code Generator 📱</h1>
      <p class="page-sub">Crie QR Codes personalizados para links, textos, WiFi, contatos e mais</p>
    </div>

    <!-- Generator Form -->
    <div class="card generator-section">
      <h3>🎨 Gerar Novo QR Code</h3>
      <form @submit.prevent="generateQr" class="generator-form">
        <div class="form-row">
          <div class="form-group">
            <label>Tipo de QR Code *</label>
            <select v-model="form.type" required>
              <option value="url">🌐 URL / Link</option>
              <option value="text">📝 Texto Livre</option>
              <option value="wifi">📶 WiFi</option>
              <option value="contact">👤 Contato (vCard)</option>
              <option value="email">📧 Email</option>
              <option value="phone">📞 Telefone</option>
              <option value="sms">💬 SMS</option>
              <option value="location">📍 Localização</option>
              <option value="event">📅 Evento</option>
            </select>
          </div>
        </div>

        <!-- Dynamic Fields based on type -->
        <div v-if="form.type === 'url'" class="fields-group">
          <div class="form-group">
            <label>URL *</label>
            <input v-model="form.data.url" type="url" placeholder="https://exemplo.com" required />
          </div>
        </div>

        <div v-if="form.type === 'text'" class="fields-group">
          <div class="form-group">
            <label>Texto *</label>
            <textarea v-model="form.data.text" rows="4" placeholder="Digite seu texto aqui..." required></textarea>
          </div>
        </div>

        <div v-if="form.type === 'wifi'" class="fields-group">
          <div class="form-row">
            <div class="form-group">
              <label>SSID (Nome da rede) *</label>
              <input v-model="form.data.ssid" type="text" placeholder="MinhaRede" required />
            </div>
            <div class="form-group">
              <label>Senha *</label>
              <input v-model="form.data.password" type="password" placeholder="senha123" required />
            </div>
          </div>
          <div class="form-group">
            <label>Tipo de Criptografia</label>
            <select v-model="form.data.encryption">
              <option value="WPA">WPA/WPA2</option>
              <option value="WEP">WEP</option>
              <option value="nopass">Sem Senha</option>
            </select>
          </div>
          <div class="form-group">
            <label><input type="checkbox" v-model="form.data.hidden" /> Rede Oculta (Hidden)</label>
          </div>
        </div>

        <div v-if="form.type === 'contact'" class="fields-group">
          <div class="form-row">
            <div class="form-group">
              <label>Nome *</label>
              <input v-model="form.data.name" type="text" placeholder="João Silva" required />
            </div>
            <div class="form-group">
              <label>Empresa</label>
              <input v-model="form.data.org" type="text" placeholder="Empresa Ltda" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Telefone</label>
              <input v-model="form.data.phone" type="tel" placeholder="+55 11 99999-9999" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="form.data.email" type="email" placeholder="contato@email.com" />
            </div>
          </div>
          <div class="form-group">
            <label>Endereço</label>
            <textarea v-model="form.data.address" rows="2" placeholder="Rua Exemplo, 123 - Cidade, Estado"></textarea>
          </div>
          <div class="form-group">
            <label>Site</label>
            <input v-model="form.data.url" type="url" placeholder="https://site.com" />
          </div>
          <div class="form-group">
            <label>Nota</label>
            <textarea v-model="form.data.note" rows="2" placeholder="Observações..."></textarea>
          </div>
        </div>

        <div v-if="form.type === 'email'" class="fields-group">
          <div class="form-row">
            <div class="form-group">
              <label>Email *</label>
              <input v-model="form.data.email" type="email" placeholder="destino@email.com" required />
            </div>
            <div class="form-group">
              <label>Assunto</label>
              <input v-model="form.data.subject" type="text" placeholder="Assunto do email" />
            </div>
          </div>
          <div class="form-group">
            <label>Corpo do Email</label>
            <textarea v-model="form.data.body" rows="3" placeholder="Corpo da mensagem..."></textarea>
          </div>
        </div>

        <div v-if="form.type === 'phone'" class="fields-group">
          <div class="form-group">
            <label>Telefone *</label>
            <input v-model="form.data.phone" type="tel" placeholder="+55 11 99999-9999" required />
          </div>
        </div>

        <div v-if="form.type === 'sms'" class="fields-group">
          <div class="form-row">
            <div class="form-group">
              <label>Telefone *</label>
              <input v-model="form.data.phone" type="tel" placeholder="+55 11 99999-9999" required />
            </div>
          </div>
          <div class="form-group">
            <label>Mensagem</label>
            <textarea v-model="form.data.body" rows="2" placeholder="Texto da mensagem..."></textarea>
          </div>
        </div>

        <div v-if="form.type === 'location'" class="fields-group">
          <div class="form-row">
            <div class="form-group">
              <label>Latitude *</label>
              <input v-model="form.data.latitude" type="number" step="any" placeholder="-23.5505" required />
            </div>
            <div class="form-group">
              <label>Longitude *</label>
              <input v-model="form.data.longitude" type="number" step="any" placeholder="-46.6333" required />
            </div>
          </div>
          <div class="form-group">
            <label>Label (opcional)</label>
            <input v-model="form.data.label" type="text" placeholder="Meu Local" />
          </div>
          <div class="form-group">
            <label>Query (para busca no Google Maps)</label>
            <input v-model="form.data.query" type="text" placeholder="Rua Exemplo, 123" />
          </div>
        </div>

        <div v-if="form.type === 'event'" class="fields-group">
          <div class="form-group">
            <label>Título do Evento *</label>
            <input v-model="form.data.title" type="text" placeholder="Reunião de Equipe" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Início *</label>
              <input v-model="form.data.start" type="datetime-local" required />
            </div>
            <div class="form-group">
              <label>Fim *</label>
              <input v-model="form.data.end" type="datetime-local" required />
            </div>
          </div>
          <div class="form-group">
            <label>Local</label>
            <input v-model="form.data.location" type="text" placeholder="Sala de Reunião 1, Escritório" />
          </div>
          <div class="form-group">
            <label>Descrição</label>
            <textarea v-model="form.data.description" rows="2" placeholder="Detalhes do evento..."></textarea>
          </div>
        </div>

        <!-- Customization Options -->
        <div class="customization-section">
          <h4>🎨 Personalização</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Cor do QR Code</label>
              <div class="color-picker">
                <input v-model="options.color" type="color" />
                <span>{{ options.color }}</span>
              </div>
            </div>
            <div class="form-group">
              <label>Cor de Fundo</label>
              <div class="color-picker">
                <input v-model="options.background" type="color" />
                <span>{{ options.background }}</span>
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Tamanho (px)</label>
              <input v-model="options.size" type="number" min="100" max="1000" step="50" />
            </div>
            <div class="form-group">
              <label>Margem (módulos)</label>
              <input v-model="options.margin" type="number" min="0" max="10" step="1" />
            </div>
          </div>
          <div class="form-group">
            <label>Nível de Correção de Erro</label>
            <select v-model="options.correctionLevel">
              <option value="L">L - 7% (Baixo)</option>
              <option value="M">M - 15% (Médio)</option>
              <option value="Q">Q - 25% (Alto)</option>
              <option value="H">H - 30% (Máximo)</option>
            </select>
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" v-model="options.logo" /> Adicionar Logo Central (experimental)
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" :disabled="generating">
          {{ generating ? 'Gerando...' : '✨ Gerar QR Code' }}
        </button>
      </form>
    </div>

    <!-- Generated QR Code Display -->
    <div v-if="qrDataUrl" class="card result-section">
      <div class="result-header">
        <h3>✅ QR Code Gerado</h3>
        <div class="result-actions">
          <button class="btn btn-secondary" @click="copyToClipboard">📋 Copiar Imagem</button>
          <a :href="qrDataUrl" :download="downloadName" class="btn btn-primary">⬇ Baixar PNG</a>
          <button class="btn btn-secondary" @click="downloadSvg">📄 Baixar SVG</button>
        </div>
      </div>

      <div class="qr-display">
        <div class="qr-container">
          <img :src="qrDataUrl" :alt="'QR Code: ' + form.type" class="qr-image" />
        </div>
        <div class="qr-info">
          <p><strong>Tipo:</strong> {{ typeLabel(form.type) }}</p>
          <p><strong>Tamanho:</strong> {{ options.size }}px</p>
          <p><strong>Correção:</strong> {{ options.correctionLevel }}</p>
        </div>
      </div>

      <!-- Scan Test -->
      <div class="scan-test">
        <h4>📱 Teste o QR Code</h4>
        <p>Escaneie com a câmera do seu celular para testar</p>
        <div class="qr-small">
          <img :src="qrDataUrl" :alt="'QR Code pequeno'" width="120" height="120" />
        </div>
      </div>
    </div>

    <!-- History -->
    <div class="card history-section">
      <div class="section-header">
        <h3>📜 Histórico Recente</h3>
        <button v-if="history.length > 0" class="btn btn-secondary btn-sm" @click="clearHistory">Limpar</button>
      </div>
      <div v-if="history.length === 0" class="empty-state">Nenhum QR Code gerado ainda</div>
      <div v-else class="history-grid">
        <div v-for="(item, index) in history" :key="index" class="history-item" @click="restoreFromHistory(item)">
          <div class="history-preview">
            <img :src="item.dataUrl" alt="" width="60" height="60" />
          </div>
          <div class="history-info">
            <span class="history-type">{{ typeLabel(item.type) }}</span>
            <span class="history-time">{{ formatTime(item.timestamp) }}</span>
            <span class="history-content">{{ truncate(item.content, 40) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import QRCode from 'qrcode';

const generating = ref(false);
const qrDataUrl = ref('');
const qrSvg = ref('');
const history = ref<any[]>([]);

const form = ref({
  type: 'url',
  data: {
    url: '',
    text: '',
    ssid: '',
    password: '',
    encryption: 'WPA',
    hidden: false,
    name: '',
    org: '',
    phone: '',
    email: '',
    address: '',
    note: '',
    subject: '',
    body: '',
    latitude: '',
    longitude: '',
    label: '',
    query: '',
    title: '',
    start: '',
    end: '',
    location: '',
    description: '',
  },
});

const options = ref({
  size: 256,
  margin: 4,
  color: '#000000',
  background: '#ffffff',
  correctionLevel: 'M',
  logo: false,
});

const generateQr = async () => {
  if (generating.value) return;
  
  generating.value = true;
  
  try {
    const content = buildQrContent();
    if (!content) {
      alert('Preencha os campos obrigatórios');
      return;
    }

    // Generate PNG
    const pngOptions = {
      width: options.value.size,
      margin: options.value.margin,
      color: {
        dark: options.value.color,
        light: options.value.background,
      },
      errorCorrectionLevel: options.value.correctionLevel as QRCode.QRCodeErrorCorrectionLevel,
    };

    qrDataUrl.value = await QRCode.toDataURL(content, pngOptions);
    
    // Generate SVG for download
    qrSvg.value = await QRCode.toString(content, {
      type: 'svg',
      width: options.value.size,
      margin: options.value.margin,
      color: {
        dark: options.value.color,
        light: options.value.background,
      },
      errorCorrectionLevel: options.value.correctionLevel as QRCode.QRCodeErrorCorrectionLevel,
    });

    // Add to history
    addToHistory();
    
  } catch (e: unknown) {
    console.error('Erro ao gerar QR:', e);
    alert('Erro ao gerar QR Code: ' + (e instanceof Error ? e.message : String(e)));
  } finally {
    generating.value = false;
  }
};

const buildQrContent = (): string => {
  const { type, data } = form.value;
  
  switch (type) {
    case 'url':
      return data.url.startsWith('http') ? data.url : `https://${data.url}`;
    
    case 'text':
      return data.text;
    
    case 'wifi':
      const authType = data.encryption === 'nopass' ? 'nopass' : data.encryption;
      const hidden = data.hidden ? ';H:true' : '';
      return `WIFI:T:${authType};S:${data.ssid};P:${data.password}${hidden};;`;
    
    case 'contact':
      let vcard = 'BEGIN:VCARD\nVERSION:3.0\n';
      if (data.name) vcard += `FN:${data.name}\n`;
      if (data.org) vcard += `ORG:${data.org}\n`;
      if (data.phone) vcard += `TEL:${data.phone}\n`;
      if (data.email) vcard += `EMAIL:${data.email}\n`;
      if (data.address) vcard += `ADR:${data.address}\n`;
      if (data.url) vcard += `URL:${data.url}\n`;
      if (data.note) vcard += `NOTE:${data.note}\n`;
      vcard += 'END:VCARD';
      return vcard;
    
    case 'email':
      let mailto = `mailto:${data.email}`;
      const params = [];
      if (data.subject) params.push(`subject=${encodeURIComponent(data.subject)}`);
      if (data.body) params.push(`body=${encodeURIComponent(data.body)}`);
      if (params.length) mailto += '?' + params.join('&');
      return mailto;
    
    case 'phone':
      return `tel:${data.phone}`;
    
    case 'sms':
      return `sms:${data.phone}${data.body ? `?body=${encodeURIComponent(data.body)}` : ''}`;
    
    case 'location':
      return `geo:${data.latitude},${data.longitude}${data.query ? `?q=${encodeURIComponent(data.query)}` : ''}`;
    
    case 'event':
      const formatDate = (dt: string) => dt.replace(/[-:]/g, '').replace('T', 'T') + 'Z';
      return `BEGIN:VEVENT\nDTSTART:${formatDate(data.start)}\nDTEND:${formatDate(data.end)}\nSUMMARY:${data.title}\nLOCATION:${data.location || ''}\nDESCRIPTION:${data.description || ''}\nEND:VEVENT`;
    
    default:
      return '';
  }
};

const addToHistory = () => {
  const item = {
    type: form.value.type,
    content: buildQrContent(),
    dataUrl: qrDataUrl.value,
    timestamp: Date.now(),
  };
  history.value.unshift(item);
  if (history.value.length > 20) history.value.pop();
  localStorage.setItem('qr_history', JSON.stringify(history.value.slice(0, 20)));
};

const copyToClipboard = async () => {
  try {
    const blob = await fetch(qrDataUrl.value).then(r => r.blob());
    await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
    alert('Imagem copiada para a área de transferência!');
  } catch (e: unknown) {
    alert('Erro ao copiar: ' + (e instanceof Error ? e.message : String(e)));
  }
};

const downloadSvg = () => {
  const link = document.createElement('a');
  link.href = 'data:image/svg+xml;base64,' + btoa(qrSvg.value);
  link.download = downloadName.value;
  link.click();
};

const downloadName = computed(() => {
  const typeLabels: Record<string, string> = {
    url: 'qr-url',
    text: 'qr-text',
    wifi: 'qr-wifi',
    contact: 'qr-contato',
    email: 'qr-email',
    phone: 'qr-telefone',
    sms: 'qr-sms',
    location: 'qr-localizacao',
    event: 'qr-evento',
  };
  return `qr-${typeLabels[form.value.type] || 'codigo'}-${Date.now()}.png`;
});

const typeLabel = (type: string) => {
  const labels: Record<string, string> = {
    url: 'URL / Link',
    text: 'Texto Livre',
    wifi: 'WiFi',
    contact: 'Contato (vCard)',
    email: 'Email',
    phone: 'Telefone',
    sms: 'SMS',
    location: 'Localização',
    event: 'Evento',
  };
  return labels[type] || type;
};

const formatTime = (ts: number) => new Date(ts).toLocaleTimeString('pt-BR');
const truncate = (str: string, len: number) => str.length > len ? str.slice(0, len) + '...' : str;

const restoreFromHistory = (item: any) => {
  form.value.type = item.type;
  // Would need to parse content back to form fields - simplified
  qrDataUrl.value = item.dataUrl;
};

const clearHistory = () => {
  if (confirm('Limpar todo o histórico?')) {
    history.value = [];
    localStorage.removeItem('qr_history');
  }
};

onMounted(() => {
  const saved = localStorage.getItem('qr_history');
  if (saved) {
    try {
      history.value = JSON.parse(saved);
    } catch (e) {
      localStorage.removeItem('qr_history');
    }
  }
});
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.generator-section { padding: 1.5rem; }
.generator-form { display: flex; flex-direction: column; gap: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--ink); }
.form-group select, .form-group input, .form-group textarea { width: 100%; padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); }
.fields-group { padding: 1rem; background: var(--surface-2); border-radius: var(--r-sm); border: 1px solid var(--hairline); }
.customization-section { border-top: 1px solid var(--hairline); padding-top: 1.5rem; margin-top: 1rem; }
.customization-section h4 { margin-bottom: 1rem; color: var(--ink); }
.color-picker { display: flex; align-items: center; gap: 0.75rem; }
.color-picker input { width: 50px; height: 36px; border: none; border-radius: var(--r-sm); cursor: pointer; }
.color-picker span { font-family: monospace; font-size: 0.85rem; color: var(--ink-muted); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-lg { padding: 1rem 2rem; font-size: 1rem; width: 100%; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { filter: brightness(1.1); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.result-section { padding: 1.5rem; }
.result-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.result-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.qr-display { display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap; gap: 2rem; }
.qr-container { background: white; padding: 1rem; border-radius: var(--r-lg); box-shadow: var(--shadow-sm); }
.qr-image { display: block; max-width: 100%; }
.qr-info { flex: 1; min-width: 200px; }
.qr-info p { margin: 0.5rem 0; color: var(--ink-muted); font-size: 0.9rem; }
.scan-test { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--hairline); text-align: center; }
.scan-test h4 { margin-bottom: 0.5rem; }
.scan-test p { color: var(--ink-muted); margin-bottom: 1rem; }
.qr-small { display: inline-block; }
.history-section { padding: 1.5rem; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.history-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
.history-item { display: flex; gap: 1rem; padding: 1rem; background: var(--surface-2); border-radius: var(--r-sm); cursor: pointer; transition: all 0.2s; }
.history-item:hover { background: var(--surface-3); }
.history-preview { flex-shrink: 0; }
.history-preview img { border-radius: var(--r-sm); border: 1px solid var(--hairline); background: white; }
.history-info { display: flex; flex-direction: column; gap: 0.25rem; min-width: 0; }
.history-type { font-size: 0.7rem; font-weight: 600; color: var(--accent); text-transform: uppercase; }
.history-time { font-size: 0.75rem; color: var(--ink-muted); }
.history-content { font-size: 0.8rem; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.empty-state { text-align: center; padding: 2rem; color: var(--ink-muted); }
</style>