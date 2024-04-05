class TaxiUtils {
  formatPhone(phone) {
    if (!phone) {
      return '';
    }

    return phone.replace(/(\d{3})(\d{2})(\d{2})(\d{2})(\d{3})/, '+$1 $2 $3 $4 $5');
  }
}

export default new TaxiUtils();

