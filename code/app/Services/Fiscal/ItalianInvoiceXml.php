<?php

namespace App\Services\Fiscal;

use App\Models\FiscalProfile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ItalianInvoiceXml
{
    public function recipient(array $data): array
    {
        $result = Validator::make($data, [
            'name' => 'required|string|max:80', 'vat_id' => ['nullable', 'regex:/^\d{11}$/'],
            'tax_code' => ['nullable', 'regex:/^(?:[A-Z0-9]{16}|\d{11})$/'], 'address' => 'required|string|max:60', 'zip' => ['required', 'regex:/^\d{5}$/'],
            'city' => 'required|string|max:60', 'province' => ['required', 'regex:/^[A-Z]{2}$/'],
            'sdi' => ['required', 'regex:/^[A-Z0-9]{7}$/'], 'pec' => 'nullable|email|max:256',
        ])->validate();
        if (! filled($result['vat_id'] ?? null) && ! filled($result['tax_code'] ?? null)) {
            throw ValidationException::withMessages(['fiscal' => 'Inserisci partita IVA o codice fiscale del destinatario.']);
        }
        if (filled($result['pec'] ?? null) && $result['sdi'] !== '0000000') {
            throw ValidationException::withMessages(['fiscal' => 'Per il recapito PEC usa codice destinatario 0000000.']);
        }

        return $result;
    }

    public function build(FiscalProfile $profile, array $snapshot, array $buyer, string $number, string $date, ?array $creditOf = null): string
    {
        $buyer = $this->recipient($buyer);
        if (! preg_match('/^RF(0[1-9]|1[0-9])$/', $profile->tax_regime ?? '')) {
            throw ValidationException::withMessages(['fiscal' => 'Configura il regime fiscale dell’azienda prima di fatturare.']);
        }
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $root = $doc->createElementNS('http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2', 'p:FatturaElettronica');
        $root->setAttribute('versione', 'FPR12');
        $doc->appendChild($root);
        $header = $this->node($root, 'FatturaElettronicaHeader');
        $tx = $this->node($header, 'DatiTrasmissione');
        $id = $this->node($tx, 'IdTrasmittente');
        $this->node($id, 'IdPaese', 'IT');
        $this->node($id, 'IdCodice', $profile->fiscal_id);
        $this->node($tx, 'ProgressivoInvio', substr(hash('sha256', $number), 0, 10));
        $this->node($tx, 'FormatoTrasmissione', 'FPR12');
        $this->node($tx, 'CodiceDestinatario', $buyer['sdi']);
        if (filled($buyer['pec'] ?? null)) {
            $this->node($tx, 'PECDestinatario', $buyer['pec']);
        }
        $seller = $this->node($header, 'CedentePrestatore');
        $details = $this->node($seller, 'DatiAnagrafici');
        $vat = $this->node($details, 'IdFiscaleIVA');
        $this->node($vat, 'IdPaese', 'IT');
        $this->node($vat, 'IdCodice', $profile->fiscal_id);
        $name = $this->node($details, 'Anagrafica');
        $this->node($name, 'Denominazione', $profile->name);
        $this->node($details, 'RegimeFiscale', $profile->tax_regime);
        $this->address($this->node($seller, 'Sede'), ['address' => $profile->address['street_address'].' '.$profile->address['street_number'], 'zip' => $profile->address['zip_code'], 'city' => $profile->address['city'], 'province' => $profile->address['province']]);
        $customer = $this->node($header, 'CessionarioCommittente');
        $details = $this->node($customer, 'DatiAnagrafici');
        if (filled($buyer['vat_id'] ?? null)) {
            $vat = $this->node($details, 'IdFiscaleIVA');
            $this->node($vat, 'IdPaese', 'IT');
            $this->node($vat, 'IdCodice', $buyer['vat_id']);
        }
        if (filled($buyer['tax_code'] ?? null)) {
            $this->node($details, 'CodiceFiscale', $buyer['tax_code']);
        }
        $name = $this->node($details, 'Anagrafica');
        $this->node($name, 'Denominazione', $buyer['name']);
        $this->address($this->node($customer, 'Sede'), $buyer);
        $body = $this->node($root, 'FatturaElettronicaBody');
        $general = $this->node($body, 'DatiGenerali');
        $document = $this->node($general, 'DatiGeneraliDocumento');
        $this->node($document, 'TipoDocumento', $creditOf ? 'TD04' : 'TD01');
        $this->node($document, 'Divisa', 'EUR');
        $this->node($document, 'Data', $date);
        $this->node($document, 'Numero', $number);
        $this->node($document, 'ImportoTotaleDocumento', FiscalMoney::decimal($snapshot['total_cents']));
        $this->node($document, 'Causale', 'RistoPilot incasso '.$snapshot['batch_id']);
        if ($creditOf) {
            $linked = $this->node($general, 'DatiFattureCollegate');
            $this->node($linked, 'IdDocumento', $creditOf['number']);
            $this->node($linked, 'Data', $creditOf['date']);
        }
        $goods = $this->node($body, 'DatiBeniServizi');
        $taxes = [];
        // Each group is a real monetary part of this payer's frozen scope. One
        // net line per VAT group avoids multiplying rounded net unit prices.
        foreach ($snapshot['groups'] as $rate => $gross) {
            if (! $gross) {
                continue;
            }
            if ((int) $rate === 0) {
                throw ValidationException::withMessages(['fiscal' => 'Le fatture con IVA zero richiedono natura e regime specifici, da completare prima dell’invio.']);
            }
            $net = intdiv($gross * 100 + intdiv(100 + (int) $rate, 2), 100 + (int) $rate);
            $vat = $gross - $net;
            $names = collect($snapshot['items'])->where('rate', (int) $rate)->pluck('description')->unique()->implode(', ');
            $line = $this->node($goods, 'DettaglioLinee');
            $this->node($line, 'NumeroLinea', (string) (count($taxes) + 1));
            $this->node($line, 'Descrizione', mb_substr($names, 0, 1000));
            $this->node($line, 'Quantita', '1.00');
            $this->node($line, 'PrezzoUnitario', FiscalMoney::decimal($net));
            $this->node($line, 'PrezzoTotale', FiscalMoney::decimal($net));
            $this->node($line, 'AliquotaIVA', number_format($rate, 2, '.', ''));
            $taxes[] = ['rate' => $rate, 'net' => $net, 'vat' => $vat];
        }
        foreach ($taxes as $tax) {
            $group = $this->node($goods, 'DatiRiepilogo');
            $this->node($group, 'AliquotaIVA', number_format($tax['rate'], 2, '.', ''));
            $this->node($group, 'ImponibileImporto', FiscalMoney::decimal($tax['net']));
            $this->node($group, 'Imposta', FiscalMoney::decimal($tax['vat']));
            $this->node($group, 'EsigibilitaIVA', 'I');
        }

        return $doc->saveXML();
    }

    private function node(\DOMElement $parent, string $name, ?string $text = null): \DOMElement
    {
        $element = $parent->ownerDocument->createElement($name);
        if ($text !== null) {
            $element->appendChild($parent->ownerDocument->createTextNode($text));
        } $parent->appendChild($element);

        return $element;
    }

    private function address(\DOMElement $parent, array $data): void
    {
        $this->node($parent, 'Indirizzo', mb_substr($data['address'], 0, 60));
        $this->node($parent, 'CAP', $data['zip']);
        $this->node($parent, 'Comune', $data['city']);
        $this->node($parent, 'Provincia', $data['province']);
        $this->node($parent, 'Nazione', 'IT');
    }
}
