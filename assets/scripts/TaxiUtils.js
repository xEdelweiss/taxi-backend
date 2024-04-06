class TaxiUtils {
  formatPhone(phone) {
    if (!phone) {
      return '';
    }

    return phone.replace(/(\d{3})(\d{2})(\d{2})(\d{2})(\d{3})/, '+$1 $2 $3 $4 $5');
  }

  formatMoney(money) {
    if (!money.cost) {
      return '';
    }

    const formatter = new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: money.currency,
    });

    return formatter.format(money.cost);
  }
}

export default new TaxiUtils();

